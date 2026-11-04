<?php
/** @var App\Shop $shop */
$orders = $shop->orders();
$ov = $shop->reports()->overview();
$m = $ov['month'];
$prev = $ov['previousMonth'];
$series = $ov['series'];
$ready = array_filter($orders, fn($o) => $o['status'] === 'ready');
$upcoming = array_filter($orders, fn($o) => in_array($o['status'], ['new', 'production'], true));
usort($upcoming, fn($a, $b) => strcmp($a['dueDate'], $b['dueDate']));
$weekdays = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
$kpi = function (string $label, string $value, string $hint, string $ico, string $tone): string {
    return "<div class=\"panel kpi\"><div class=\"label\"><span class=\"ico {$tone}\">" . icon($ico) . "</span>{$label}</div>"
        . "<div class=\"num\">{$value}</div><div class=\"hint\">{$hint}</div></div>";
};
?>
<div class="page-head">
  <div>
    <h1>Painel</h1>
    <p><?= $weekdays[(int) date('w')] ?>, <?= date('j') ?> de <?= mb_strtolower(month_name(date('Y-m'), false)) ?></p>
  </div>
</div>

<div class="kpis">
  <?= $kpi('Entrou no mês', money($m['revenue'], false), 'mês passado: ' . money($prev['revenue'], false), 'arrowDown', 'tone-blue') ?>
  <?= $kpi('Gastos do mês', money($m['expenses'], false), 'mês passado: ' . money($prev['expenses'], false), 'arrowUp', 'tone-amber') ?>
  <?= $kpi('Lucro do mês',
      '<span class="' . ($m['profit'] < 0 ? 'text-red' : '') . '">' . money($m['profit'], false) . '</span>',
      $m['profit'] >= 0 ? 'margem de ' . number_format($m['margin'], 1, ',', '.') . '%' : 'por enquanto saiu mais do que entrou',
      'coins', $m['profit'] < 0 ? 'tone-red' : 'tone-green') ?>
  <?= $kpi('A receber', money($ov['receivable'], false), 'saldo de pedidos já feitos', 'wallet', 'tone-brand') ?>
</div>

<div class="flow">
  <a href="?p=pedidos#novos" class="panel" style="--c:var(--blue)">
    <span class="big"><?= $ov['counts']['new'] ?></span>
    <span class="what"><b>Novos pedidos</b><span class="small muted">aguardando começar</span></span>
    <span class="arrow"><?= icon('chevronR') ?></span>
  </a>
  <a href="?p=pedidos#pendentes" class="panel" style="--c:var(--amber)">
    <span class="big"><?= $ov['counts']['production'] ?></span>
    <span class="what"><b>Pendentes</b><span class="small <?= $ov['late'] ? 'text-red' : 'muted' ?>">
      <?= $ov['late'] ? $ov['late'] . ' atrasado' . ($ov['late'] > 1 ? 's' : '') : 'tudo no prazo' ?></span></span>
    <span class="arrow"><?= icon('chevronR') ?></span>
  </a>
  <a href="?p=pedidos#prontos" class="panel" style="--c:var(--green)">
    <span class="big"><?= $ov['counts']['ready'] ?></span>
    <span class="what"><b>Finalizados</b><span class="small muted">prontos para entrega</span></span>
    <span class="arrow"><?= icon('chevronR') ?></span>
  </a>
</div>

<div class="cols-2">
  <div class="grid">
    <div class="panel">
      <div class="panel-head"><h2>Entradas e gastos</h2><?php require __DIR__ . '/_legend.php' ?></div>
      <div class="panel-body"><?php require __DIR__ . '/_chart.php' ?></div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Próximos prazos</h2><span class="small muted">novos e pendentes</span></div>
      <div class="table-wrap" style="margin-top:12px">
        <?php if ($upcoming): ?>
        <table>
          <thead><tr><th>Pedido</th><th>Cliente</th><th>Prazo</th><th>Status</th><th class="right">Total</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($upcoming, 0, 8) as $o): ?>
            <tr>
              <td><a class="row-link" href="?p=pedido&amp;id=<?= e($o['id']) ?>">#<?= $o['number'] ?></a></td>
              <td><?= e($o['customer']) ?><div class="small muted"><?= e(describe_items($o)) ?></div></td>
              <td><?= due_badge($o) ?></td>
              <td><?= status_pill($o['status']) ?></td>
              <td class="right nowrap"><?= money($o['total']) ?></td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
        <?php else: ?><p class="empty" style="padding:0 18px 18px">Nenhum pedido em andamento.</p><?php endif ?>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Prontos para entrega</h2><span class="pill st-ready"><?= count($ready) ?></span></div>
    <div class="panel-body">
      <?php if ($ready): ?>
      <ul class="list">
        <?php foreach ($ready as $o): ?>
        <li class="row-item">
          <a class="main-col" href="?p=pedido&amp;id=<?= e($o['id']) ?>">
            <b>#<?= $o['number'] ?> · <?= e($o['customer']) ?></b>
            <span class="small muted ellipsis"><?= e(describe_items($o)) ?></span>
            <?= pay_badge($o) ?>
          </a>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="order_advance">
            <input type="hidden" name="id" value="<?= e($o['id']) ?>">
            <button class="btn small green"><?= icon('check') ?>Entregar</button>
          </form>
        </li>
        <?php endforeach ?>
      </ul>
      <?php else: ?><p class="empty">Nenhum pedido esperando entrega.</p><?php endif ?>
    </div>
  </div>
</div>
