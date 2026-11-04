<?php
/**
 * PH Gráfica – Gestão
 *
 * Pedidos (novos, pendentes, prontos para entrega, entregues), gastos e lucros.
 * Projeto só com arquivos PHP: sem JavaScript, framework ou Composer. PHP 8.1+.
 *
 * Rodar:   php -S 127.0.0.1:8091   e abrir http://127.0.0.1:8091
 * Dados:   ficam em data/*.php (cada arquivo devolve um array). Na primeira vez são
 *          criados seis meses de exemplo; para começar do zero, apague a pasta data/.
 * Lucro:   regime de caixa. Entrou = pagamentos recebidos no mês; lucro = entrou - gastos do mês.
 *
 * index.php            recebe os formulários (POST) e escolhe a tela
 * src/helpers.php      formatação, etapas do pedido, CSRF, mensagens, logo
 * src/Shop.php         validação e gravação de pedidos, pagamentos e gastos
 * src/Reports.php      entradas, gastos, lucro e saldo a receber
 * src/Store.php        gravação dos dados em data/*.php com trava e troca atômica
 * src/Seeder.php       dados de exemplo, tabela de preços, categorias e formas de pagamento
 * views/               uma tela por arquivo; views/_estilo.php é o estilo, views/_chart.php o gráfico
 */
declare(strict_types=1);

spl_autoload_register(function (string $class) {
    $file = __DIR__ . '/src/' . basename(str_replace('\\', '/', $class)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
require __DIR__ . '/src/helpers.php';

use App\{Store, Shop};

date_default_timezone_set('America/Sao_Paulo');
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$shop = new Shop(new Store(__DIR__ . '/data'));
$page = $_GET['p'] ?? 'painel';
$error = null;
$form = null; // dados do formulário de pedido para mostrar de novo quando há erro

// Ações (formulários). Depois de gravar, redireciona para não reenviar ao atualizar a página.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (string) ($_POST['id'] ?? '');
    try {
        if (!csrf_ok()) {
            throw new InvalidArgumentException('A página expirou. Tente de novo.');
        }
        switch ($action) {
            case 'order_save':
                $form = $shop->orderInput($_POST);
                if (isset($_POST['more_lines'])) {
                    // Só pediu mais linhas: mostra o formulário de novo sem salvar.
                    $form['extraLines'] = (int) ($_POST['extra'] ?? 0) + 3;
                    $page = $id ? 'editar' : 'novo';
                    break;
                }
                $order = $shop->saveOrder($form, $id ?: null);
                flash($id ? 'Pedido atualizado.' : "Pedido #{$order['number']} criado.");
                redirect(['p' => 'pedido', 'id' => $order['id']]);

            case 'order_advance':
                $decided = isset($_POST['payment_decided']);
                // Recebe antes de mudar o status: se o valor for inválido, o pedido não muda.
                if ($decided && (float) str_replace(',', '.', (string) ($_POST['receive'] ?? '')) > 0) {
                    $shop->pay($id, $_POST['receive'], $_POST['method'] ?? '');
                }
                $result = $shop->advance($id, $decided);
                if ($result['needsPayment']) {
                    redirect(['p' => 'pedido', 'id' => $id, 'entregar' => 1]);
                }
                flash("Pedido #{$result['order']['number']}: " . mb_strtolower(STATUS[$result['status']]['label']) . '.');
                redirect(($_POST['back'] ?? '') === 'pedidos' ? ['p' => 'pedidos'] : ['p' => 'pedido', 'id' => $id]);

            case 'order_pay':
                $shop->pay($id, $_POST['amount'] ?? '', $_POST['method'] ?? '');
                flash('Pagamento registrado.');
                redirect(['p' => 'pedido', 'id' => $id]);

            case 'order_notes':
                $shop->saveNotes($id, $_POST['notes'] ?? '');
                flash('Observação salva.');
                redirect(['p' => 'pedido', 'id' => $id]);

            case 'order_delete':
                $order = $shop->order($id);
                $shop->deleteOrder($id);
                flash("Pedido #{$order['number']} excluído.");
                redirect(['p' => 'pedidos']);

            case 'expense_add':
                $expense = $shop->addExpense($_POST);
                flash('Gasto lançado.');
                redirect(['p' => 'gastos', 'mes' => substr($expense['date'], 0, 7)]);

            case 'expense_delete':
                $expense = $shop->expense($id);
                $shop->deleteExpense($id);
                flash('Gasto apagado.');
                redirect(['p' => 'gastos', 'mes' => substr($expense['date'] ?? today(), 0, 7)]);

            default:
                throw new InvalidArgumentException('Ação desconhecida.');
        }
    } catch (InvalidArgumentException $ex) {
        $error = $ex->getMessage();
        // Erro no formulário de pedido: mostra o formulário com o que foi digitado.
        if ($action === 'order_save') {
            $page = $id ? 'editar' : 'novo';
        } elseif (in_array($action, ['order_advance', 'order_pay', 'order_notes'], true)) {
            $page = 'pedido';
            $_GET['id'] = $id;
        } elseif ($action === 'expense_add') {
            $page = 'gastos';
            $_GET['mes'] = substr((string) ($_POST['date'] ?? ''), 0, 7);
        }
    }
}

$pages = [
    'painel' => 'Painel',
    'pedidos' => 'Pedidos',
    'pedido' => 'Pedido',
    'novo' => 'Novo pedido',
    'editar' => 'Editar pedido',
    'gastos' => 'Gastos',
    'lucros' => 'Lucros',
];
if (!isset($pages[$page])) {
    $page = 'painel';
}
$message = flash();

ob_start();
require __DIR__ . "/views/{$page}.php";
$content = ob_get_clean();

require __DIR__ . '/views/layout.php';
