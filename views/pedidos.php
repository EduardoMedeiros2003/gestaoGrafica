<?php
/** @var App\Shop $shop */
$orders = $shop->orders();
$open = count(array_filter($orders, fn($o) => in_array($o['status'], OPEN_STATUSES, true)));
$columns = [
    'new' => ['novos', 'var(--blue)'],
    'production' => ['pendentes', 'var(--amber)'],
    'ready' => ['prontos', 'var(--green)'],
    'delivered' => ['entregues', 'var(--faint)'],
];
$showAllDelivered = isset($_GET['todos']);
?>
<div class="page-head">
  <div><h1>Pedidos</h1><p><?= $open ?> em aberto</p></div>
</div>

<div class="board">
  <?php foreach ($columns as $status => [$anchor, $color]):
      $cards = array_values(array_filter($orders, fn($o) => $o['status'] === $status));
      if ($status === 'delivered') {
          usort($cards, fn($a, $b) => strcmp((string) $b['deliveredAt'], (string) $a['deliveredAt']));
      } else {
          usort($cards, fn($a, $b) => strcmp($a['dueDate'], $b['dueDate']));
      }
      $total = count($cards);
      $limit = $status === 'delivered' && !$showAllDelivered ? 8 : PHP_INT_MAX; ?>
    <section class="col" id="<?= $anchor ?>" style="--c:<?= $color ?>">
      <div class="col-head"><span class="bullet"></span><b><?= STATUS[$status]['column'] ?></b><span class="count"><?= $total ?></span></div>

      <?php foreach (array_slice($cards, 0, $limit) as $o): ?>
        <article class="card">
          <a class="card-link" href="?p=pedido&amp;id=<?= e($o['id']) ?>">
            <span class="top"><span class="num">#<?= $o['number'] ?></span>
              <?= $status === 'delivered' ? 'entregue ' . dmy($o['deliveredAt'], false) : due_badge($o) ?></span>
            <span class="customer"><?= e($o['customer']) ?></span>
            <span class="desc"><?= e(describe_items($o)) ?></span>
            <span class="money"><span class="total"><?= money($o['total']) ?></span><?= pay_badge($o) ?></span>
          </a>
          <?php if (STATUS[$status]['next']): ?>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="order_advance">
            <input type="hidden" name="id" value="<?= e($o['id']) ?>">
            <input type="hidden" name="back" value="pedidos">
            <button class="btn small next <?= $status === 'ready' ? 'green' : '' ?>"><?= STATUS[$status]['action'] ?></button>
          </form>
          <?php endif ?>
        </article>
      <?php endforeach ?>

      <?php if (!$cards): ?><p class="empty small" style="text-align:center">Nenhum pedido.</p><?php endif ?>
      <?php if ($total > $limit): ?>
        <a class="col-more" href="?p=pedidos&amp;todos=1#entregues">ver todos os <?= $total ?></a>
      <?php endif ?>
    </section>
  <?php endforeach ?>
</div>
