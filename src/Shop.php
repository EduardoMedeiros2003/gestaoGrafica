<?php
declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Regras de pedidos e gastos. Erros de validação viram InvalidArgumentException
 * com a mensagem que aparece para o usuário.
 */
final class Shop
{
    public array $settings;

    public function __construct(private Store $db)
    {
        if (!$db->exists('orders')) {
            Seeder::run($db);
        }
        $this->settings = $db->all('settings');
    }

    /** Pedidos do mais novo para o mais antigo, já com o total pago. */
    public function orders(): array
    {
        $orders = array_map(fn($o) => $o + ['paid' => Reports::paid($o)], $this->db->all('orders'));
        usort($orders, fn($a, $b) => $b['number'] <=> $a['number']);
        return $orders;
    }

    public function order(string $id): ?array
    {
        $o = $this->db->find('orders', $id);
        return $o ? $o + ['paid' => Reports::paid($o)] : null;
    }

    public function expenses(): array
    {
        $rows = $this->db->all('expenses');
        usort($rows, fn($a, $b) => strcmp($b['date'], $a['date']));
        return $rows;
    }

    public function reports(): Reports
    {
        return new Reports($this->db->all('orders'), $this->db->all('expenses'));
    }

    /**
     * Lê o formulário de pedido. Linhas de item em branco são ignoradas;
     * se o produto está na tabela e o preço veio vazio, usa o preço da tabela.
     */
    public function orderInput(array $post): array
    {
        $prices = [];
        foreach ($this->settings['products'] as $p) {
            $prices[mb_strtolower($p['name'])] = $p['price'];
        }

        $items = [];
        $products = $post['items']['product'] ?? [];
        foreach ((array) $products as $n => $product) {
            $product = trim((string) $product);
            $qty = trim((string) ($post['items']['qty'][$n] ?? ''));
            $price = str_replace(',', '.', trim((string) ($post['items']['price'][$n] ?? '')));
            if ($product === '' && $price === '') {
                continue;
            }
            if ($price === '' && isset($prices[mb_strtolower($product)])) {
                $price = (string) $prices[mb_strtolower($product)];
            }
            $items[] = ['product' => $product, 'qty' => $qty === '' ? '1' : str_replace(',', '.', $qty), 'price' => $price];
        }

        return [
            'customer' => trim((string) ($post['customer'] ?? '')),
            'phone' => trim((string) ($post['phone'] ?? '')),
            'dueDate' => (string) ($post['dueDate'] ?? ''),
            'notes' => trim((string) ($post['notes'] ?? '')),
            'deposit' => str_replace(',', '.', trim((string) ($post['deposit'] ?? ''))),
            'method' => (string) ($post['method'] ?? ''),
            'items' => $items,
        ];
    }

    public static function inputTotal(array $input): float
    {
        $total = 0.0;
        foreach ($input['items'] as $i) {
            if (is_numeric($i['qty']) && is_numeric($i['price'])) {
                $total += (float) $i['qty'] * (float) $i['price'];
            }
        }
        return round($total, 2);
    }

    /** Cria (sem $id) ou altera um pedido. Devolve o pedido salvo. */
    public function saveOrder(array $input, ?string $id = null): array
    {
        $customer = $input['customer'];
        if ($customer === '' || mb_strlen($customer) > 120) {
            throw new InvalidArgumentException('Informe o nome do cliente.');
        }
        if (mb_strlen($input['phone']) > 30) {
            throw new InvalidArgumentException('Telefone muito longo.');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['dueDate']) || !strtotime($input['dueDate'])) {
            throw new InvalidArgumentException('Informe o prazo de entrega.');
        }
        if (!$input['items']) {
            throw new InvalidArgumentException('Adicione pelo menos um item.');
        }
        $items = [];
        foreach ($input['items'] as $n => $i) {
            $line = $n + 1;
            if ($i['product'] === '' || mb_strlen($i['product']) > 120) {
                throw new InvalidArgumentException("Descreva o item {$line}.");
            }
            if (!is_numeric($i['qty']) || $i['qty'] <= 0) {
                throw new InvalidArgumentException("Quantidade inválida no item {$line}.");
            }
            if (!is_numeric($i['price']) || $i['price'] < 0) {
                throw new InvalidArgumentException("Informe o preço do item {$line}.");
            }
            $q = (float) $i['qty'];
            $items[] = ['product' => $i['product'], 'qty' => floor($q) == $q ? (int) $q : round($q, 2), 'price' => round((float) $i['price'], 2)];
        }
        $total = round(array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $items)), 2);
        $fields = [
            'customer' => $customer, 'phone' => $input['phone'], 'dueDate' => $input['dueDate'],
            'notes' => mb_substr($input['notes'], 0, 1000), 'items' => $items, 'total' => $total,
        ];

        if ($id) {
            $before = $this->order($id) ?? throw new InvalidArgumentException('Pedido não encontrado.');
            if ($total < $before['paid']) {
                throw new InvalidArgumentException('O novo total ficou menor do que o cliente já pagou (' . \money($before['paid']) . ').');
            }
            $this->db->update('orders', $id, $fields);
            return $this->order($id);
        }

        $payments = [];
        if ($input['deposit'] !== '' && (float) $input['deposit'] > 0) {
            if (!is_numeric($input['deposit']) || (float) $input['deposit'] > $total) {
                throw new InvalidArgumentException('O sinal não pode ser maior que o total.');
            }
            $payments[] = ['date' => \today(), 'amount' => round((float) $input['deposit'], 2), 'method' => $this->method($input['method'])];
        }
        return $this->db->mutate('orders', function (array &$rows) use ($fields, $payments) {
            $order = $fields + [
                'id' => Store::id('o'),
                'number' => $rows ? max(array_column($rows, 'number')) + 1 : 1001,
                'payments' => $payments, 'status' => 'new',
                'createdAt' => date('Y-m-d\TH:i:s'), 'deliveredAt' => null,
            ];
            $rows[] = $order;
            return $order;
        });
    }

    public function pay(string $id, mixed $amount, mixed $method): void
    {
        $o = $this->order($id) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        $amount = str_replace(',', '.', trim((string) $amount));
        $left = \remaining($o);
        if (!is_numeric($amount) || (float) $amount <= 0) {
            throw new InvalidArgumentException('Informe o valor recebido.');
        }
        if ((float) $amount > $left) {
            throw new InvalidArgumentException('Valor maior do que falta pagar (' . \money($left) . ').');
        }
        $o['payments'][] = ['date' => \today(), 'amount' => round((float) $amount, 2), 'method' => $this->method($method)];
        $this->db->update('orders', $id, ['payments' => $o['payments']]);
    }

    /** Leva o pedido para a próxima etapa. Entregar com saldo exige decidir o pagamento antes. */
    public function advance(string $id, bool $paymentDecided): array
    {
        $o = $this->order($id) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        $next = \STATUS[$o['status']]['next'] ?? null;
        if (!$next) {
            throw new InvalidArgumentException('Esse pedido já foi entregue.');
        }
        if ($next === 'delivered' && \remaining($o) > 0 && !$paymentDecided) {
            return ['needsPayment' => true, 'order' => $o];
        }
        $this->db->update('orders', $id, ['status' => $next, 'deliveredAt' => $next === 'delivered' ? \today() : null]);
        return ['needsPayment' => false, 'order' => $o, 'status' => $next];
    }

    public function saveNotes(string $id, mixed $notes): void
    {
        $this->order($id) ?? throw new InvalidArgumentException('Pedido não encontrado.');
        $this->db->update('orders', $id, ['notes' => mb_substr(trim((string) $notes), 0, 1000)]);
    }

    public function deleteOrder(string $id): void
    {
        $this->db->delete('orders', $id) || throw new InvalidArgumentException('Pedido não encontrado.');
    }

    public function addExpense(array $post): array
    {
        $date = (string) ($post['date'] ?? '');
        $category = (string) ($post['category'] ?? '');
        $description = trim((string) ($post['description'] ?? ''));
        $amount = str_replace(',', '.', trim((string) ($post['amount'] ?? '')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            throw new InvalidArgumentException('Data inválida.');
        }
        if (!in_array($category, $this->settings['expenseCategories'], true)) {
            throw new InvalidArgumentException('Escolha uma categoria.');
        }
        if ($description === '' || mb_strlen($description) > 160) {
            throw new InvalidArgumentException('Descreva o gasto.');
        }
        if (!is_numeric($amount) || (float) $amount <= 0) {
            throw new InvalidArgumentException('Informe o valor do gasto.');
        }
        return $this->db->insert('expenses', [
            'id' => Store::id('e'), 'date' => $date, 'category' => $category,
            'description' => $description, 'amount' => round((float) $amount, 2),
        ]);
    }

    public function expense(string $id): ?array
    {
        return $this->db->find('expenses', $id);
    }

    public function deleteExpense(string $id): void
    {
        $this->db->delete('expenses', $id) || throw new InvalidArgumentException('Gasto não encontrado.');
    }

    private function method(mixed $m): string
    {
        return in_array($m, $this->settings['paymentMethods'], true) ? $m : $this->settings['paymentMethods'][0];
    }
}
