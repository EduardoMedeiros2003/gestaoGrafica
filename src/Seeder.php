<?php
declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Dados de exemplo: seis meses de pedidos e gastos, com datas relativas a hoje.
 * Usa semente fixa para o exemplo sair sempre igual.
 */
final class Seeder
{
    public static function settings(): array
    {
        return [
            'company' => 'PH Gráfica',
            'products' => [
                ['name' => 'Cartão de visita (milheiro)', 'price' => 90],
                ['name' => 'Panfleto A5 (milheiro)', 'price' => 180],
                ['name' => 'Banner em lona (m²)', 'price' => 85],
                ['name' => 'Adesivo vinil (m²)', 'price' => 70],
                ['name' => 'Fachada em ACM (m²)', 'price' => 450],
                ['name' => 'Wind banner', 'price' => 280],
                ['name' => 'Convite (unidade)', 'price' => 6.5],
                ['name' => 'Cardápio plastificado', 'price' => 18],
                ['name' => 'Bloco de notas 50 folhas', 'price' => 12],
                ['name' => 'Apostila encadernada', 'price' => 22],
                ['name' => 'Etiqueta (cento)', 'price' => 25],
                ['name' => 'Camiseta sublimada', 'price' => 35],
            ],
            'expenseCategories' => ['Papel e mídia', 'Tinta e toner', 'Salários', 'Aluguel', 'Energia', 'Água e internet', 'Manutenção', 'Impostos', 'Outros'],
            'paymentMethods' => ['Pix', 'Dinheiro', 'Cartão', 'Boleto'],
        ];
    }

    public static function run(Store $db): void
    {
        mt_srand(42);
        $settings = self::settings();
        $price = array_column($settings['products'], 'price', 'name');
        $today = new DateTimeImmutable('today');

        $customers = [
            ['Padaria Pão de Mel', '(11) 98123-4410'], ['Academia Força Total', '(11) 97455-1022'],
            ['Igreja Batista Central', '(11) 99812-7731'], ['Clínica Sorriso', '(11) 98777-0391'],
            ['Pizzaria Bella Napoli', '(11) 96644-2210'], ['Escola Pequeno Saber', '(11) 98320-5567'],
            ['Mercadinho São Jorge', '(11) 97001-8823'], ['Ana Paula (casamento)', '(11) 99123-6604'],
            ['Auto Center Rocha', '(11) 98555-4127'], ['Imobiliária Lar Doce', '(11) 97789-3390'],
            ['Pet Shop Amigo Fiel', '(11) 96512-7480'], ['Lanchonete do Zé', '(11) 98890-1145'],
            ['Salão Bela Mulher', '(11) 97234-6608'], ['Construtora Base Forte', '(11) 99456-2273'],
            ['Marcos Vieira (aniversário)', '(11) 98011-9932'], ['Farmácia Saúde+', '(11) 97660-5519'],
            ['Colégio Objetivo Norte', '(11) 98402-3376'], ['Ótica Visão', '(11) 96778-0045'],
        ];

        // Pedidos típicos: lista de [produto, quantidade mínima, quantidade máxima].
        $jobs = [
            [['Cartão de visita (milheiro)', 1, 3]],
            [['Panfleto A5 (milheiro)', 2, 10]],
            [['Banner em lona (m²)', 2, 6]],
            [['Fachada em ACM (m²)', 3, 8], ['Adesivo vinil (m²)', 1, 3]],
            [['Convite (unidade)', 80, 250]],
            [['Cardápio plastificado', 10, 40], ['Adesivo vinil (m²)', 1, 2]],
            [['Wind banner', 1, 2], ['Banner em lona (m²)', 1, 3]],
            [['Apostila encadernada', 20, 80]],
            [['Camiseta sublimada', 10, 50]],
            [['Etiqueta (cento)', 5, 20], ['Cartão de visita (milheiro)', 1, 2]],
            [['Bloco de notas 50 folhas', 20, 100]],
        ];

        $orders = [];
        $number = 1000;
        $makeOrder = function (DateTimeImmutable $created, string $status) use (&$number, $customers, $jobs, $price, $settings, $today): array {
            [$customer, $phone] = $customers[mt_rand(0, count($customers) - 1)];
            $items = [];
            foreach ($jobs[mt_rand(0, count($jobs) - 1)] as [$product, $min, $max]) {
                $items[] = ['product' => $product, 'qty' => mt_rand($min, $max), 'price' => $price[$product]];
            }
            $total = round(array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $items)), 2);
            $due = $created->modify('+' . mt_rand(2, 8) . ' days');
            $method = $settings['paymentMethods'][mt_rand(0, 3)];

            // Metade paga tudo na hora; o resto deixa 50% de sinal.
            $payments = [];
            $fullUpfront = mt_rand(0, 1) === 1;
            $payments[] = ['date' => $created->format('Y-m-d'), 'amount' => $fullUpfront ? $total : round($total / 2, 2), 'method' => $method];

            $deliveredAt = null;
            if ($status === 'delivered') {
                $deliveredAt = $due->modify('-' . mt_rand(0, 1) . ' days');
                if ($deliveredAt > $today) {
                    $deliveredAt = $today;
                }
                if (!$fullUpfront) {
                    $payments[] = ['date' => $deliveredAt->format('Y-m-d'), 'amount' => round($total - $total / 2, 2), 'method' => $method];
                }
            }
            if ($status === 'new' && mt_rand(0, 2) === 0) {
                $payments = []; // ainda não pagou nada
            }

            return [
                'id' => Store::id('o'),
                'number' => ++$number,
                'customer' => $customer,
                'phone' => $phone,
                'items' => $items,
                'total' => $total,
                'payments' => $payments,
                'dueDate' => $due->format('Y-m-d'),
                'status' => $status,
                'notes' => '',
                'createdAt' => $created->format('Y-m-d') . 'T' . sprintf('%02d:%02d:00', mt_rand(8, 17), mt_rand(0, 59)),
                'deliveredAt' => $deliveredAt?->format('Y-m-d'),
            ];
        };

        // Histórico: pedidos entregues nos últimos 6 meses.
        for ($daysAgo = 180; $daysAgo >= 8; $daysAgo -= mt_rand(1, 2)) {
            $orders[] = $makeOrder($today->modify("-{$daysAgo} days"), 'delivered');
        }
        // Pedidos em andamento.
        foreach ([[7, 'delivered'], [6, 'delivered'], [5, 'ready'], [4, 'ready'], [3, 'ready'], [6, 'production'], [5, 'production'],
                  [4, 'production'], [3, 'production'], [2, 'production'], [1, 'production'], [1, 'new'], [0, 'new'], [0, 'new'], [0, 'new']] as [$ago, $status]) {
            $orders[] = $makeOrder($today->modify("-{$ago} days"), $status);
        }
        $orders[count($orders) - 9]['notes'] = 'Cliente pediu prova antes de imprimir.';
        $orders[count($orders) - 3]['notes'] = 'Arte enviada por WhatsApp.';

        $expenses = [];
        for ($m = 5; $m >= 0; $m--) {
            $month = $today->modify('first day of this month')->modify("-{$m} months");
            $add = function (int $day, string $category, string $description, float $amount) use (&$expenses, $month, $today) {
                $date = $month->modify('+' . ($day - 1) . ' days');
                if ($date <= $today) {
                    $expenses[] = ['id' => Store::id('e'), 'date' => $date->format('Y-m-d'), 'category' => $category,
                        'description' => $description, 'amount' => $amount];
                }
            };
            $add(1, 'Aluguel', 'Aluguel do galpão', 2800);
            $add(5, 'Salários', 'Folha de pagamento (2 funcionários)', 5200);
            $add(8, 'Papel e mídia', 'Couchê 150g e 300g', mt_rand(1400, 2200));
            $add(12, 'Energia', 'Conta de luz', mt_rand(620, 940));
            $add(2, 'Água e internet', 'Água + internet fibra', 260);
            $add(15, 'Tinta e toner', 'Tinta solvente e toner', mt_rand(900, 1600));
            $add(20, 'Impostos', 'DAS Simples Nacional', mt_rand(950, 1300));
            $add(22, 'Papel e mídia', 'Lona e vinil', mt_rand(700, 1300));
            if (mt_rand(0, 2) === 0) {
                $add(18, 'Manutenção', 'Manutenção da plotter', mt_rand(350, 900));
            }
            if (mt_rand(0, 1) === 0) {
                $add(25, 'Outros', 'Material de limpeza e escritório', mt_rand(80, 220));
            }
        }

        $db->put('settings', $settings);
        $db->put('orders', $orders);
        $db->put('expenses', $expenses);
    }
}
