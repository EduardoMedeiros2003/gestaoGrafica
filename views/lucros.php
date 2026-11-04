<?php
/** @var App\Shop $shop */
$series = $shop->reports()->series(6);
$months = array_column($series, 'month');
$month = valid_month($_GET['mes'] ?? null);
if (!in_array($month, $months, true)) {
    $month = end($months);
}
$m = $series[array_search($month, $months, true)];
$first = $months[0];
$last = end($months);
$maxCat = max([1, ...array_values($m['byCategory'])]);
$kpi = function (string $label, string $value, string $hint, string $ico, string $tone): string {
    return "<div class=\"panel kpi\"><div class=\"label\"><span class=\"ico {$tone}\">" . icon($ico) . "</span>{$label}</div>"
        . "<div class=\"num\">{$value}</div><div class=\"hint\">{$hint}</div></div>";
};
?>
<div class="page-head">
  <div><h1>Lucros</h1><p>Dinheiro que entrou menos o que foi gasto<?= $month === $last ? ' · mês em andamento' : '' ?></p></div>
  <div class="tools">
    <nav class="month-nav" aria-label="Mês">
      <?php if ($month > $first): ?>
        <a href="?p=lucros&amp;mes=<?= shift_month($month, -1) ?>" aria-label="Mês anterior"><?= icon('chevronL') ?></a>
      <?php else: ?><span class="disabled"><?= icon('chevronL') ?></span><?php endif ?>
      <b><?= month_name($month) ?></b>
      <?php if ($month < $last): ?>
        <a href="?p=lucros&amp;mes=<?= shift_month($month, 1) ?>" aria-label="Próximo mês"><?= icon('chevronR') ?></a>
      <?php else: ?><span class="disabled"><?= icon('chevronR') ?></span><?php endif ?>
    </nav>
  </div>
</div>

<div class="kpis">
  <?= $kpi('Entrou', money($m['revenue'], false), $m['delivered'] . ' pedidos entregues', 'arrowDown', 'tone-blue') ?>
  <?= $kpi('Gastos', money($m['expenses'], false), count($m['byCategory']) . ' categorias', 'arrowUp', 'tone-amber') ?>
  <?= $kpi('Lucro', '<span class="' . ($m['profit'] < 0 ? 'text-red' : '') . '">' . money($m['profit'], false) . '</span>',
      $m['profit'] < 0 ? 'saiu mais do que entrou' : 'sobrou no caixa', 'coins', $m['profit'] < 0 ? 'tone-red' : 'tone-green') ?>
  <?= $kpi('Margem', $m['revenue'] ? number_format($m['margin'], 1, ',', '.') . '%' : '–', 'do que entrou, quanto sobrou', 'chart', 'tone-brand') ?>
</div>

<div class="panel" style="margin-bottom:20px">
  <div class="panel-head"><h2>Últimos 6 meses</h2><?php require __DIR__ . '/_legend.php' ?></div>
  <div class="panel-body"><?php require __DIR__ . '/_chart.php' ?></div>
</div>

<div class="cols-even">
  <div class="panel">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Mês</th><th class="right">Entrou</th><th class="right">Gastos</th><th class="right">Lucro</th><th class="right">Margem</th></tr></thead>
        <tbody>
        <?php foreach (array_reverse($series) as $s): ?>
          <tr class="<?= $s['month'] === $month ? 'selected' : '' ?>">
            <td><a class="row-link" href="?p=lucros&amp;mes=<?= $s['month'] ?>"><?= month_name($s['month']) ?></a></td>
            <td class="right nowrap"><?= money($s['revenue'], false) ?></td>
            <td class="right nowrap"><?= money($s['expenses'], false) ?></td>
            <td class="right nowrap <?= $s['profit'] < 0 ? 'text-red' : 'text-green' ?>"><?= money($s['profit'], false) ?></td>
            <td class="right"><?= $s['revenue'] ? number_format($s['margin'], 1, ',', '.') . '%' : '–' ?></td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Para onde foi o dinheiro</h2><span class="small muted"><?= month_name($month, false) ?></span></div>
    <div class="panel-body">
      <?php foreach ($m['byCategory'] as $cat => $v): ?>
        <div class="cat-row">
          <div class="line"><span><?= e($cat) ?></span>
            <span class="nowrap"><b><?= money($v, false) ?></b> <span class="small muted"><?= $m['expenses'] ? round($v / $m['expenses'] * 100) : 0 ?>%</span></span></div>
          <div class="track"><i style="width:<?= round($v / $maxCat * 100, 1) ?>%"></i></div>
        </div>
      <?php endforeach ?>
      <?php if (!$m['byCategory']): ?><p class="empty">Sem gastos neste mês.</p><?php endif ?>
    </div>
  </div>
</div>
