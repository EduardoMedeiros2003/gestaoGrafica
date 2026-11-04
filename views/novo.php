<?php
/**
 * Formulário de pedido. Serve para criar (p=novo) e editar (p=editar).
 * @var App\Shop $shop @var ?array $form @var string $page
 */
$editing = $page === 'editar';
$order = $editing ? $shop->order((string) ($_GET['id'] ?? $_POST['id'] ?? '')) : null;
if ($editing && !$order) {
    echo '<div class="alert error">Pedido não encontrado.</div>';
    return;
}

// Valores: o que acabou de ser enviado (erro ou "mais linhas"), o pedido salvo, ou em branco.
$values = $form ?? [
    'customer' => $order['customer'] ?? '',
    'phone' => $order['phone'] ?? '',
    'dueDate' => $order['dueDate'] ?? date('Y-m-d', strtotime('+3 days')),
    'notes' => $order['notes'] ?? '',
    'deposit' => '',
    'method' => '',
    'items' => array_map(fn($i) => ['product' => $i['product'], 'qty' => (string) $i['qty'], 'price' => (string) $i['price']], $order['items'] ?? []),
];
$extra = $values['extraLines'] ?? 0;
$blankLines = max(3 - count($values['items']), 1) + $extra;
$lines = array_merge($values['items'], array_fill(0, $blankLines, ['product' => '', 'qty' => '', 'price' => '']));
$total = App\Shop::inputTotal($values);
$customers = [];
foreach ($shop->orders() as $o) {
    $customers[$o['customer']] ??= $o['phone'];
}
$priceInput = fn(string $v) => $v === '' ? '' : (is_numeric($v) ? number_format((float) $v, 2, ',', '') : $v);
?>
<div class="page-head">
  <div>
    <a class="back-link" href="<?= $editing ? '?p=pedido&amp;id=' . e($order['id']) : '?p=pedidos' ?>"><?= icon('back') ?><?= $editing ? 'Pedido #' . $order['number'] : 'Pedidos' ?></a>
    <h1><?= $editing ? 'Editar pedido #' . $order['number'] : 'Novo pedido' ?></h1>
  </div>
</div>

<form method="post" class="panel order-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="order_save">
  <input type="hidden" name="extra" value="<?= (int) $extra ?>">
  <!-- Enter num campo usa o primeiro botão do formulário: este, que salva (e não o "Mais linhas"). -->
  <button class="sr-only" tabindex="-1" aria-hidden="true">Salvar</button>
  <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($order['id']) ?>"><?php endif ?>

  <div class="panel-body">
    <div class="form-grid">
      <label class="field span-2">Cliente
        <input name="customer" list="clientes" required maxlength="120" value="<?= e($values['customer']) ?>" placeholder="Nome do cliente ou empresa"></label>
      <label class="field">Telefone
        <input name="phone" maxlength="30" value="<?= e($values['phone']) ?>" placeholder="(11) 99999-9999"></label>
      <label class="field">Prazo de entrega
        <input name="dueDate" type="date" required value="<?= e($values['dueDate']) ?>"></label>
      <?php if (!$editing): ?>
      <label class="field">Sinal recebido (R$)
        <input name="deposit" inputmode="decimal" value="<?= e($values['deposit']) ?>" placeholder="0,00"></label>
      <label class="field">Forma de pagamento
        <select name="method"><?php foreach ($shop->settings['paymentMethods'] as $mt): ?>
          <option <?= $values['method'] === $mt ? 'selected' : '' ?>><?= e($mt) ?></option><?php endforeach ?></select></label>
      <?php endif ?>
    </div>
    <datalist id="clientes"><?php foreach (array_keys($customers) as $c): ?><option value="<?= e($c) ?>"><?php endforeach ?></datalist>
    <datalist id="produtos"><?php foreach ($shop->settings['products'] as $p): ?><option value="<?= e($p['name']) ?>"><?php endforeach ?></datalist>

    <h2 style="margin:24px 0 4px">Itens</h2>
    <p class="small muted" style="margin:0 0 12px">Escolhendo um produto da tabela, pode deixar o preço em branco: ele é preenchido ao salvar. Linhas vazias são ignoradas.</p>
    <div class="item-line head"><span>Produto ou serviço</span><span>Qtd</span><span>Preço unit. (R$)</span></div>
    <?php foreach ($lines as $line): ?>
      <div class="item-line">
        <input name="items[product][]" list="produtos" maxlength="120" value="<?= e($line['product']) ?>" placeholder="Ex.: Banner em lona (m²)">
        <input name="items[qty][]" inputmode="decimal" value="<?= e($line['qty']) ?>" placeholder="1">
        <input name="items[price][]" inputmode="decimal" value="<?= e($priceInput($line['price'])) ?>" placeholder="tabela">
      </div>
    <?php endforeach ?>
    <button class="btn small ghost" name="more_lines" value="1" formnovalidate><?= icon('plus') ?>Mais linhas</button>

    <?php if ($total > 0): ?>
    <div class="order-total"><span class="muted">Total até agora</span><b><?= money($total) ?></b></div>
    <?php endif ?>

    <label class="field" style="margin-top:18px">Observações
      <textarea name="notes" maxlength="1000" placeholder="Arte, acabamento, endereço de entrega…"><?= e($values['notes']) ?></textarea></label>

    <div class="price-table">
      <h2>Tabela de preços</h2>
      <ul>
        <?php foreach ($shop->settings['products'] as $p): ?>
          <li><span><?= e($p['name']) ?></span><b><?= money($p['price']) ?></b></li>
        <?php endforeach ?>
      </ul>
    </div>

    <div class="buttons">
      <a class="btn ghost" href="<?= $editing ? '?p=pedido&amp;id=' . e($order['id']) : '?p=pedidos' ?>">Cancelar</a>
      <button class="btn primary"><?= $editing ? 'Salvar alterações' : 'Criar pedido' ?></button>
    </div>
  </div>
</form>
