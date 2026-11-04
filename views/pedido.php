<?php
/** @var App\Shop $shop */
$o = $shop->order((string) ($_GET['id'] ?? ''));
if (!$o) {
    echo '<div class="alert error">Pedido não encontrado.</div><a class="btn" href="?p=pedidos">' . icon('back') . 'Voltar para pedidos</a>';
    return;
}
$left = remaining($o);
$next = STATUS[$o['status']]['next'];
$flow = array_keys(STATUS);
$step = array_search($o['status'], $flow, true);
$askDelivery = isset($_GET['entregar']) && $next === 'delivered' && $left > 0;
$confirmDelete = isset($_GET['excluir']);
$methods = $shop->settings['paymentMethods'];
$hidden = csrf_field() . '<input type="hidden" name="id" value="' . e($o['id']) . '">';
?>
<div class="page-head">
  <div>
    <a class="back-link" href="?p=pedidos"><?= icon('back') ?>Pedidos</a>
    <h1>Pedido #<?= $o['number'] ?> · <?= e($o['customer']) ?></h1>
    <p>Feito em <?= dmy($o['createdAt']) ?><?= $o['phone'] ? ' · ' . e($o['phone']) : '' ?></p>
  </div>
  <div class="tools">
    <?= status_pill($o['status']) ?>
    <?= due_badge($o) ?: ($o['deliveredAt'] ? '<span class="pill tone-gray">entregue em ' . dmy($o['deliveredAt']) . '</span>' : '') ?>
  </div>
</div>

<?php if ($confirmDelete): ?>
<div class="panel confirm">
  <div>
    <h2>Excluir o pedido #<?= $o['number'] ?>?</h2>
    <p class="muted">Os pagamentos dele também saem do financeiro. Não dá para desfazer.</p>
  </div>
  <form method="post" class="actions" style="margin:0">
    <?= $hidden ?><input type="hidden" name="action" value="order_delete">
    <a class="btn ghost" href="?p=pedido&amp;id=<?= e($o['id']) ?>">Voltar</a>
    <button class="btn danger-solid"><?= icon('trash') ?>Excluir</button>
  </form>
</div>
<?php endif ?>

<?php if ($askDelivery): ?>
<div class="panel confirm" id="entregar">
  <div>
    <h2>Entregar o pedido #<?= $o['number'] ?></h2>
    <p class="muted"><?= e($o['customer']) ?> ainda deve <b class="text-red"><?= money($left) ?></b>. Está pagando agora?</p>
  </div>
  <form method="post" class="deliver-form">
    <?= $hidden ?><input type="hidden" name="action" value="order_advance"><input type="hidden" name="payment_decided" value="1">
    <label class="field">Recebendo agora (R$)<input name="receive" inputmode="decimal" value="<?= number_format($left, 2, ',', '') ?>"></label>
    <label class="field">Forma<select name="method"><?php foreach ($methods as $mt): ?><option><?= e($mt) ?></option><?php endforeach ?></select></label>
    <button class="btn green"><?= icon('check') ?>Entregar</button>
  </form>
  <p class="small muted" style="margin:10px 0 0">Para entregar sem receber agora, deixe o valor em 0.</p>
</div>
<?php endif ?>

<div class="cols-2">
  <div class="grid">
    <div class="panel">
      <div class="panel-head"><h2>Andamento</h2></div>
      <div class="panel-body">
        <div class="steps">
          <?php foreach ($flow as $i => $st): ?>
            <div class="<?= $i <= $step ? 'done' : '' ?>"><?= STATUS[$st]['label'] ?></div>
          <?php endforeach ?>
        </div>
        <div class="actions">
          <?php if ($next && !$askDelivery): ?>
          <form method="post">
            <?= $hidden ?><input type="hidden" name="action" value="order_advance">
            <button class="btn <?= $next === 'delivered' ? 'green' : 'primary' ?>"><?= $next === 'delivered' ? icon('check') : '' ?><?= STATUS[$o['status']]['action'] ?></button>
          </form>
          <?php endif ?>
          <?php if ($o['status'] !== 'delivered'): ?>
            <a class="btn" href="?p=editar&amp;id=<?= e($o['id']) ?>"><?= icon('edit') ?>Editar pedido</a>
          <?php endif ?>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Itens</h2><span class="small muted">prazo <?= dmy($o['dueDate']) ?></span></div>
      <div class="table-wrap" style="margin-top:12px">
        <table>
          <thead><tr><th>Produto</th><th class="right">Qtd</th><th class="right">Unitário</th><th class="right">Subtotal</th></tr></thead>
          <tbody>
          <?php foreach ($o['items'] as $item): ?>
            <tr>
              <td><?= e($item['product']) ?></td>
              <td class="right"><?= qty($item['qty']) ?></td>
              <td class="right nowrap"><?= money($item['price']) ?></td>
              <td class="right nowrap"><?= money($item['qty'] * $item['price']) ?></td>
            </tr>
          <?php endforeach ?>
          </tbody>
          <tfoot><tr><td colspan="3">Total</td><td class="right nowrap"><?= money($o['total']) ?></td></tr></tfoot>
        </table>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Observações</h2></div>
      <form method="post" class="panel-body">
        <?= $hidden ?><input type="hidden" name="action" value="order_notes">
        <textarea name="notes" maxlength="1000" placeholder="Ex.: cliente vai mandar a arte até amanhã"><?= e($o['notes']) ?></textarea>
        <div class="actions" style="margin-top:10px"><button class="btn small">Salvar observação</button></div>
      </form>
    </div>
  </div>

  <div class="grid">
    <div class="panel">
      <div class="panel-head"><h2>Pagamento</h2></div>
      <div class="panel-body">
        <div class="pay-box">
          <div><span class="small muted">Total</span><b><?= money($o['total']) ?></b></div>
          <div><span class="small muted">Pago</span><b class="text-green"><?= money($o['paid']) ?></b></div>
          <div><span class="small muted">Falta</span><b class="<?= $left ? 'text-red' : '' ?>"><?= money($left) ?></b></div>
        </div>
        <?php if ($o['payments']): ?>
        <ul class="list payments">
          <?php foreach ($o['payments'] as $p): ?>
            <li><span><?= dmy($p['date']) ?> · <?= e($p['method']) ?></span><b><?= money($p['amount']) ?></b></li>
          <?php endforeach ?>
        </ul>
        <?php else: ?><p class="empty small">Nenhum pagamento ainda.</p><?php endif ?>

        <?php if ($left > 0): ?>
        <form method="post" class="pay-form">
          <?= $hidden ?><input type="hidden" name="action" value="order_pay">
          <label class="field">Valor recebido (R$)<input name="amount" inputmode="decimal" value="<?= number_format($left, 2, ',', '') ?>" required></label>
          <label class="field">Forma<select name="method"><?php foreach ($methods as $mt): ?><option><?= e($mt) ?></option><?php endforeach ?></select></label>
          <button class="btn">Registrar</button>
        </form>
        <?php endif ?>
      </div>
    </div>

    <?php if (!$confirmDelete): ?>
    <a class="btn ghost danger" href="?p=pedido&amp;id=<?= e($o['id']) ?>&amp;excluir=1"><?= icon('trash') ?>Excluir pedido</a>
    <?php endif ?>
  </div>
</div>
