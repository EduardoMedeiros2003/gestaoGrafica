<?php
/**
 * Barras agrupadas de entradas x gastos por mês, desenhadas em SVG no servidor.
 * Espera $series (lista de meses vinda de Reports::series).
 * @var array $series
 */
$W = 760;
$H = 250;
$pad = ['l' => 70, 'r' => 10, 't' => 10, 'b' => 30];
$plotW = $W - $pad['l'] - $pad['r'];
$plotH = $H - $pad['t'] - $pad['b'];
$current = date('Y-m');

// Escala com passos redondos (5 mil, 10 mil...) e no máximo 5 linhas.
$top = max(1, ...array_map(fn($s) => max($s['revenue'], $s['expenses']), $series));
$mag = 10 ** (floor(log10($top)) - 1);
$step = $mag;
foreach ([1, 2, 2.5, 5, 10, 20, 25, 50, 100] as $mult) {
    $step = $mult * $mag;
    if ($top / $step <= 5) {
        break;
    }
}
$max = ceil($top / $step) * $step;
$y = fn(float $v) => $pad['t'] + $plotH - ($v / $max) * $plotH;
$groupW = $plotW / count($series);
$barW = min(32, ($groupW * 0.62 - 2) / 2);

$bar = function (float $x, float $value, string $class) use ($y, $barW, $pad, $plotH): string {
    $h = $pad['t'] + $plotH - $y($value);
    if ($h <= 0) {
        return '';
    }
    $r = min(4, $barW / 2, $h);
    $top = $y($value);
    $bottom = $top + $h;
    return sprintf('<path class="%s" d="M%.1f,%.1fV%.1fQ%.1f,%.1f %.1f,%.1fH%.1fQ%.1f,%.1f %.1f,%.1fV%.1fZ"/>',
        $class, $x, $bottom, $top + $r, $x, $top, $x + $r, $top, $x + $barW - $r, $x + $barW, $top, $x + $barW, $top + $r, $bottom);
};
$axisMoney = fn(float $v) => $v >= 1000 ? 'R$ ' . number_format($v / 1000, fmod($v, 1000) ? 1 : 0, ',', '.') . ' mil' : 'R$ ' . number_format($v, 0, ',', '.');
?>
<div class="chart">
  <svg viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Entradas e gastos por mês">
    <?php for ($v = 0; $v <= $max; $v += $step): ?>
      <line class="grid-line" x1="<?= $pad['l'] ?>" x2="<?= $W - $pad['r'] ?>" y1="<?= $y($v) ?>" y2="<?= $y($v) ?>"/>
      <text class="axis-label" x="<?= $pad['l'] - 10 ?>" y="<?= $y($v) + 4 ?>" text-anchor="end"><?= $axisMoney($v) ?></text>
    <?php endfor ?>
    <?php foreach ($series as $i => $s):
        $gx = $pad['l'] + $i * $groupW;
        $x0 = $gx + ($groupW - ($barW * 2 + 2)) / 2;
        $isCurrent = $s['month'] === $current; ?>
      <g class="group">
        <title><?= e(month_name($s['month'])) ?><?= $isCurrent ? ' (até hoje)' : '' ?>&#10;Entrou: <?= money($s['revenue'], false) ?>&#10;Gastos: <?= money($s['expenses'], false) ?>&#10;Lucro: <?= money($s['profit'], false) ?></title>
        <rect class="hit" x="<?= $gx ?>" y="<?= $pad['t'] ?>" width="<?= $groupW ?>" height="<?= $plotH ?>" rx="6"/>
        <?= $bar($x0, $s['revenue'], 'bar-in') ?>
        <?= $bar($x0 + $barW + 2, $s['expenses'], 'bar-out') ?>
        <text class="axis-label" x="<?= $gx + $groupW / 2 ?>" y="<?= $H - 8 ?>" text-anchor="middle"><?= mb_strtolower(mb_substr(month_name($s['month'], false), 0, 3)) ?><?= $isCurrent ? '*' : '' ?></text>
      </g>
    <?php endforeach ?>
  </svg>
  <div class="small muted">* mês em andamento. Passe o mouse sobre um mês para ver os valores.</div>
</div>
