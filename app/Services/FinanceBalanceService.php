<?php

namespace App\Services;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Illuminate\Support\Collection;

class FinanceBalanceService
{
    /** @param Collection<int, FinanceAccount> $accounts */
    public function totalBalance(Collection $accounts): string
    {
        $accountIds = $accounts->pluck('id')->all();
        $cents = $accounts->sum(fn (FinanceAccount $account) => $this->toCents((string) $account->opening_balance));

        if ($accountIds === []) {
            return $this->fromCents($cents);
        }

        FinanceTransaction::query()
            ->where(fn ($query) => $query->whereIn('account_id', $accountIds)->orWhereIn('from_account_id', $accountIds)->orWhereIn('to_account_id', $accountIds))
            ->get(['type', 'amount', 'account_id', 'from_account_id', 'to_account_id', 'adjustment_direction'])
            ->each(function (FinanceTransaction $transaction) use ($accountIds, &$cents) {
                $amount = $this->toCents((string) $transaction->amount);
                $cents += match ($transaction->type) {
                    'income' => in_array($transaction->account_id, $accountIds, true) ? $amount : 0,
                    'expense' => in_array($transaction->account_id, $accountIds, true) ? -$amount : 0,
                    'transfer' => (in_array($transaction->to_account_id, $accountIds, true) ? $amount : 0)
                        - (in_array($transaction->from_account_id, $accountIds, true) ? $amount : 0),
                    'adjustment' => in_array($transaction->account_id, $accountIds, true)
                        ? ($transaction->adjustment_direction === 'increase' ? $amount : -$amount)
                        : 0,
                    default => 0,
                };
            });

        return $this->fromCents($cents);
    }

    public function balance(FinanceAccount $account): string
    {
        $cents = $this->toCents((string) $account->opening_balance);
        FinanceTransaction::query()
            ->where(fn ($query) => $query->where('account_id', $account->id)->orWhere('from_account_id', $account->id)->orWhere('to_account_id', $account->id))
            ->get(['type', 'amount', 'account_id', 'from_account_id', 'to_account_id', 'adjustment_direction'])
            ->each(function (FinanceTransaction $transaction) use ($account, &$cents) {
                $amount = $this->toCents((string) $transaction->amount);
                $cents += match ($transaction->type) {
                    'income' => $transaction->account_id === $account->id ? $amount : 0,
                    'expense' => $transaction->account_id === $account->id ? -$amount : 0,
                    'transfer' => $transaction->to_account_id === $account->id ? $amount : ($transaction->from_account_id === $account->id ? -$amount : 0),
                    'adjustment' => $transaction->account_id === $account->id ? ($transaction->adjustment_direction === 'increase' ? $amount : -$amount) : 0,
                    default => 0,
                };
            });

        return $this->fromCents($cents);
    }

    public function add(string ...$amounts): string
    {
        return $this->fromCents(array_sum(array_map($this->toCents(...), $amounts)));
    }

    public function subtract(string $left, string $right): string
    {
        return $this->fromCents($this->toCents($left) - $this->toCents($right));
    }

    public function toCents(string $amount): int
    {
        $negative = str_starts_with(trim($amount), '-');
        [$whole, $decimal] = array_pad(explode('.', ltrim(trim($amount), '+-'), 2), 2, '');
        $cents = ((int) ($whole ?: '0') * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }

    public function fromCents(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return $sign.intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
