<?php
/** @var string $page @var array $pages @var string $content @var ?string $message @var ?string $error @var App\Shop $shop */
$nav = ['painel' => ['Painel', 'home'], 'pedidos' => ['Pedidos', 'box'], 'gastos' => ['Gastos', 'receipt'], 'lucros' => ['Lucros', 'chart']];
$activeNav = in_array($page, ['pedido', 'novo', 'editar'], true) ? 'pedidos' : $page;
$newCount = count(array_filter($shop->orders(), fn($o) => $o['status'] === 'new'));
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pages[$page]) ?> – PH Gráfica</title>
  <link rel="icon" href="data:image/svg+xml;base64,<?= base64_encode(logo_svg()) ?>">
  <style><?php require __DIR__ . '/_estilo.php' ?></style>
</head>
<body>
<div class="shell">
  <aside class="side">
    <a class="logo" href="?p=painel">
      <?= logo_svg(32) ?>
      <span>PH Gráfica<small>Gestão</small></span>
    </a>
    <nav>
      <?php foreach ($nav as $key => [$label, $ico]): ?>
        <a href="?p=<?= $key ?>" class="<?= $activeNav === $key ? 'active' : '' ?>">
          <?= icon($ico) ?><span class="label"><?= $label ?></span>
          <?php if ($key === 'pedidos' && $newCount): ?><span class="badge" title="Pedidos novos"><?= $newCount ?></span><?php endif ?>
        </a>
      <?php endforeach ?>
    </nav>
  </aside>

  <div class="main">
    <header class="bar">
      <a class="btn primary" href="?p=novo"><?= icon('plus') ?><span class="label">Novo pedido</span></a>
    </header>
    <main>
      <?php if ($message): ?><div class="alert ok"><?= icon('check') ?><?= e($message) ?></div><?php endif ?>
      <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif ?>
      <?= $content ?>
    </main>
  </div>
</div>
</body>
</html>
