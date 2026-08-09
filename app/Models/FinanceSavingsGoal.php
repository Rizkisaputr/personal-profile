<?php

namespace App\Models;

use App\Services\FinanceBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceSavingsGoal extends Model
{
    protected $fillable = ['name', 'target_amount', 'start_date', 'target_date', 'status', 'notes'];

    protected function casts(): array
    {
        return ['target_amount' => 'decimal:2', 'start_date' => 'date', 'target_date' => 'date'];
    }

    public function monthlyTargets(): HasMany
    {
        return $this->hasMany(FinanceSavingsMonthlyTarget::class, 'savings_goal_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FinanceSavingsEntry::class, 'savings_goal_id');
    }

    public function savedAmount(?int $year = null, ?int $month = null): string
    {
        $q = $this->entries();
        if ($year && $month) {
            $q->whereYear('entry_date', $year)->whereMonth('entry_date', $month);
        }$s = app(FinanceBalanceService::class);
        $c = 0;
        foreach ($q->get() as $e) {
            $c += ($e->type === 'contribution' ? 1 : -1) * $s->toCents((string) $e->amount);
        }

        return $s->fromCents($c);
    }

    public function progress(): int
    {
        $s = app(FinanceBalanceService::class);
        $t = $s->toCents((string) $this->target_amount);

        return $t ? max(0, min(100, (int) intdiv($s->toCents($this->savedAmount()) * 100, $t))) : 0;
    }

    public function monthSummary(int $year, int $month): array
    {
        $s = app(FinanceBalanceService::class);
        $target = (string) ($this->monthlyTargets()->where(compact('year', 'month'))->value('target_amount') ?? '0.00');
        $actual = $this->savedAmount($year, $month);
        $remaining = $s->fromCents(max(0, $s->toCents($target) - $s->toCents($actual)));
        $tc = $s->toCents($target);

        return compact('target', 'actual', 'remaining') + ['progress' => $tc ? min(100, (int) intdiv(max(0, $s->toCents($actual)) * 100, $tc)) : 0];
    }

    public function previousMonthShortfall(): array
    {
        $d = now()->subMonthNoOverflow();
        $summary = $this->monthSummary($d->year, $d->month);

        return ['year' => $d->year, 'month' => $d->month, 'target' => $summary['target'], 'actual' => $summary['actual'], 'shortfall' => $summary['remaining']];
    }
}
