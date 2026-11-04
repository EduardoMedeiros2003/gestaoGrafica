<?php
declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Números do financeiro. O regime é de caixa: receita é o dinheiro que entrou
 * (pagamentos dos pedidos) no mês, e lucro é receita menos gastos do mesmo mês.
 */
final class Reports
{
    private DateTimeImmutable $today;

    public function __construct(private array $orders, private array $expenses, ?DateTimeImmutable $today = null)
    {
        $this->today = $today ?? new DateTimeImmutable('today');
    }

    public static function paid(array $order): float
    {
        return round(array_sum(array_column($order['payments'], 'amount')), 2);
    }

    public function month(string $ym): array
    {
        $revenue = 0.0;
        foreach ($this->orders as $o) {
            foreach ($o['payments'] as $p) {
                if (str_starts_with($p['date'], $ym)) {
                    $revenue += $p['amount'];
                }
            }
        }

        $expenses = 0.0;
        $byCategory = [];
        foreach ($this->expenses as $e) {
            if (str_starts_with($e['date'], $ym)) {
                $expenses += $e['amount'];
                $byCategory[$e['category']] = ($byCategory[$e['category']] ?? 0) + $e['amount'];
            }
        }
        arsort($byCategory);

        $delivered = count(array_filter($this->orders, fn($o) => $o['status'] === 'delivered' && str_starts_with((string) $o['deliveredAt'], $ym)));
        $profit = $revenue - $expenses;

        return [
            'month' => $ym,
            'revenue' => round($revenue, 2),
            'expenses' => round($expenses, 2),
            'profit' => round($profit, 2),
            'margin' => $revenue > 0 ? round($profit / $revenue * 100, 1) : 0,
            'delivered' => $delivered,
            'byCategory' => $byCategory,
        ];
    }

    /** Últimos N meses, do mais antigo para o atual. */
    public function series(int $months): array
    {
        $first = $this->today->modify('first day of this month');
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $out[] = $this->month($first->modify("-{$i} months")->format('Y-m'));
        }
        return $out;
    }

    public function overview(): array
    {
        $counts = ['new' => 0, 'production' => 0, 'ready' => 0];
        $receivable = 0.0;
        $late = 0;
        $todayYmd = $this->today->format('Y-m-d');
        foreach ($this->orders as $o) {
            if (isset($counts[$o['status']])) {
                $counts[$o['status']]++;
                $receivable += $o['total'] - self::paid($o);
                if ($o['dueDate'] < $todayYmd && $o['status'] !== 'ready') {
                    $late++;
                }
            } elseif ($o['status'] === 'delivered') {
                $receivable += $o['total'] - self::paid($o);
            }
        }
        $month = $this->month($this->today->format('Y-m'));
        $previous = $this->month($this->today->modify('first day of last month')->format('Y-m'));

        return [
            'today' => $todayYmd,
            'counts' => $counts,
            'late' => $late,
            'receivable' => round($receivable, 2),
            'month' => $month,
            'previousMonth' => $previous,
            'series' => $this->series(6),
        ];
    }
}
