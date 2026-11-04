<?php
/** @var App\Shop $shop */
$current = date('Y-m');
$month = valid_month($_GET['mes'] ?? null) ?? $current;
$all = $shop->expenses();
$rows = array_values(array_filter($all, fn($x) => str_starts_with($x['date'], $month)));
$total = array_sum(array_column($rows, 'amount'));
$prevTotal = array_sum(array_column(array_filter($all, fn($x) => str_starts_with($x['date'], shift_month($month, -1))), 'amount'));
$byCat = [];
foreach ($rows as $x) {
    $byCat[$x['category']] = ($byCat[$x['category']] ?? 0) + $x['amount'];
}
arsort($byCat);
$maxCat = max([1, ...array_values($byCat)]);
$deleting = isset($_GET['apagar']) ? $shop->expense((string) $_GET['apagar']) : null;
$posted = ($_POST['action'] ?? '') === 'expense_add' ? $_POST : [];
$defaultDate = $month === $current ? date('Y-m-d') : $month . '-01';
?>
<div class="page-head">
  <div><h1>Gastos</h1><p><?= count($rows) ?> lançamentos em <?= mb_strtolower(month_name($month)) ?></p></div>
  <div class="tools">
    <nav class="month-nav" aria-label="Mês">
      <a href="?p=gastos&amp;mes=<?= shift_month($month, -1) ?>" aria-label="Mês anterior"><?= icon('chevronL') ?></a>
      <b><?= month_name($month) ?></b>
      <?php if ($month < $current): ?>
        <a href="?p=gastos&amp;mes=<?= shift_month($month, 1) ?>" aria-label="Próximo mês"><?= icon('chevronR') ?></a>
      <?php else: ?><span class="disabled"><?= icon('chevronR') ?></span><?php endif ?>
    </nav>
  </div>
</div>

<?php if ($deleting): ?>
<div class="panel confirm">
  <div>
    <h2>Apagar este gasto?</h2>
    <p class="muted"><?= e($deleting['description']) ?> · <?= money($deleting['amount']) ?> · <?= dmy($deleting['date']) ?></p>
  </div>
  <form method="post" class="actions" style="margin:0">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="expense_delete">
    <input type="hidden" name="id" value="<?= e($deleting['id']) ?>">
    <a class="btn ghost" href="?p=gastos&amp;mes=<?= $month ?>">Voltar</a>
    <button class="btn danger-solid"><?= icon('trash') ?>Apagar</button>
  </form>
</div>
<?php endif ?>

<form method="post" class="panel" style="margin-bottom:20px">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="expense_add">
  <div class="panel-body expense-form">
    <label class="field">Data<input type="date" name="date" required value="<?= e($posted['date'] ?? $defaultDate) ?>"></label>
    <label class="field">Categoria
      <select name="category" required>
        <?php foreach ($shop->settings['expenseCategories'] as $c): ?>
          <option <?= ($posted['category'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach ?>
      </select></label>
    <label class="field grow">Descrição<input name="description" required maxlength="160" value="<?= e($posted['description'] ?? '') ?>" placeholder="Ex.: 10 resmas de couchê 150g"></label>
    <label class="field">Valor (R$)<input name="amount" inputmode="decimal" required value="<?= e($posted['amount'] ?? '') ?>" placeholder="0,00"></label>
    <button class="btn primary"><?= icon('plus') ?>Lançar gasto</button>
  </div>
</form>

<div class="cols-2">
  <div class="panel">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th class="right">Valor</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $x): ?>
          <tr>
            <td class="nowrap"><?= dmy($x['date'], false) ?></td>
            <td><?= e($x['description']) ?></td>
            <td><span class="pill tone-gray"><?= e($x['category']) ?></span></td>
            <td class="right nowrap"><?= money($x['amount']) ?></td>
            <td class="right"><a class="icon-btn" href="?p=gastos&amp;mes=<?= $month ?>&amp;apagar=<?= e($x['id']) ?>" title="Apagar"><?= icon('trash') ?></a></td>
          </tr>
        <?php endforeach ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">Nenhum gasto lançado neste mês.</td></tr><?php endif ?>
        </tbody>
        <?php if ($rows): ?>
          <tfoot><tr><td colspan="3">Total</td><td class="right nowrap"><?= money($total) ?></td><td></td></tr></tfoot>
        <?php endif ?>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Por categoria</h2></div>
    <div class="panel-body">
      <div class="kpi" style="padding:0 0 14px">
        <div class="num" style="margin-top:0"><?= money($total, false) ?></div>
        <div class="hint">mês anterior: <?= money($prevTotal, false) ?></div>
      </div>
      <?php foreach ($byCat as $cat => $v): ?>
        <div class="cat-row">
          <div class="line"><span><?= e($cat) ?></span><b class="nowrap"><?= money($v, false) ?></b></div>
          <div class="track"><i style="width:<?= round($v / $maxCat * 100, 1) ?>%"></i></div>
        </div>
      <?php endforeach ?>
      <?php if (!$byCat): ?><p class="empty">Sem gastos.</p><?php endif ?>
    </div>
  </div>
</div>
