<?php
declare(strict_types=1);

const STATUS = [
    'new' => ['label' => 'Novo', 'column' => 'Novos pedidos', 'next' => 'production', 'action' => 'Começar produção'],
    'production' => ['label' => 'Pendente', 'column' => 'Pendentes', 'next' => 'ready', 'action' => 'Finalizar'],
    'ready' => ['label' => 'Pronto p/ entrega', 'column' => 'Prontos p/ entrega', 'next' => 'delivered', 'action' => 'Entregar'],
    'delivered' => ['label' => 'Entregue', 'column' => 'Entregues', 'next' => null, 'action' => null],
];
const OPEN_STATUSES = ['new', 'production', 'ready'];

function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function money(float $v, bool $cents = true): string
{
    return ($v < 0 ? '-' : '') . 'R$ ' . number_format(abs($v), $cents ? 2 : 0, ',', '.');
}

function qty(float|int $v): string
{
    return number_format((float) $v, floor((float) $v) == $v ? 0 : 2, ',', '.');
}

function dmy(?string $date, bool $year = true): string
{
    if (!$date) {
        return '';
    }
    return date($year ? 'd/m/Y' : 'd/m', strtotime(substr($date, 0, 10)));
}

function today(): string
{
    return date('Y-m-d');
}

function month_name(string $ym, bool $year = true): string
{
    $names = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    [$y, $m] = explode('-', $ym);
    return ucfirst($names[(int) $m]) . ($year ? " de {$y}" : '');
}

function shift_month(string $ym, int $delta): string
{
    return (new DateTimeImmutable($ym . '-01'))->modify("{$delta} months")->format('Y-m');
}

function valid_month(mixed $ym): ?string
{
    return is_string($ym) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym) ? $ym : null;
}

function days_until(string $ymd): int
{
    return (int) (new DateTimeImmutable(today()))->diff(new DateTimeImmutable($ymd))->format('%r%a');
}

function url(array $params): string
{
    return '?' . http_build_query($params);
}

function redirect(array $params): never
{
    header('Location: ' . url($params), true, 303);
    exit;
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $m = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $m;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function csrf_ok(): bool
{
    return is_string($_POST['csrf'] ?? null) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function icon(string $name): string
{
    static $paths = [
        'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
        'box' => 'M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8',
        'receipt' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6M9 16h3',
        'chart' => 'M4 20V11M10 20V5M16 20v-6M21 20H3',
        'plus' => 'M12 5v14M5 12h14',
        'arrowUp' => 'M12 19V5M6 11l6-6 6 6',
        'arrowDown' => 'M12 5v14M6 13l6 6 6-6',
        'wallet' => 'M3 7h15a3 3 0 0 1 3 3v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 7l12-4v4M17 14h.01',
        'coins' => 'M9 14a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM15.5 9.6A6 6 0 1 1 9.4 15.7',
        'chevronL' => 'M15 6l-6 6 6 6',
        'chevronR' => 'M9 6l6 6-6 6',
        'back' => 'M19 12H5M11 6l-6 6 6 6',
        'trash' => 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
        'check' => 'M5 12.5l4.5 4.5L19 7',
        'edit' => 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4',
    ];
    return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $paths[$name] . '"/></svg>';
}

/** Logo da PH Gráfica: quatro pontos CMYK. Com $size vira ícone na página; sem, vai para o favicon. */
function logo_svg(?int $size = null): string
{
    $attrs = $size ? " width=\"{$size}\" height=\"{$size}\" aria-hidden=\"true\"" : '';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"' . $attrs . '>'
        . '<rect width="32" height="32" rx="8" fill="#1d2027"/>'
        . '<circle cx="11.5" cy="11.5" r="5" fill="#16a9e0"/><circle cx="20.5" cy="11.5" r="5" fill="#e0287a"/>'
        . '<circle cx="11.5" cy="20.5" r="5" fill="#f5c400"/><circle cx="20.5" cy="20.5" r="5" fill="#f4f4f4"/></svg>';
}

function remaining(array $o): float
{
    return max(0, round($o['total'] - $o['paid'], 2));
}

function describe_items(array $o): string
{
    $first = $o['items'][0];
    $extra = count($o['items']) - 1;
    return qty($first['qty']) . '× ' . $first['product'] . ($extra ? " + {$extra} " . ($extra === 1 ? 'item' : 'itens') : '');
}

function status_pill(string $status): string
{
    return '<span class="pill dot st-' . $status . '">' . STATUS[$status]['label'] . '</span>';
}

function due_badge(array $o): string
{
    if (!in_array($o['status'], OPEN_STATUSES, true)) {
        return '';
    }
    $d = days_until($o['dueDate']);
    return match (true) {
        $d < 0 => '<span class="pill tone-red">Atrasado ' . -$d . ($d === -1 ? ' dia' : ' dias') . '</span>',
        $d === 0 => '<span class="pill tone-amber">Entrega hoje</span>',
        $d === 1 => '<span class="pill tone-amber">Amanhã</span>',
        default => '<span class="pill tone-gray">' . dmy($o['dueDate'], false) . '</span>',
    };
}

function pay_badge(array $o): string
{
    $left = remaining($o);
    return $left > 0
        ? '<span class="small text-red">Falta ' . money($left) . '</span>'
        : '<span class="small text-green">Pago</span>';
}
