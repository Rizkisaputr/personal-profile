<?php

namespace App\Http\Requests\Admin;

use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $type = $this->string('type')->toString();

        return [
            'type' => ['required', Rule::in(['income', 'expense', 'transfer', 'adjustment'])],
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'decimal:0,2', 'gt:0', 'max:9999999999999999.99'],
            'category_id' => [Rule::requiredIf(in_array($type, ['income', 'expense'], true)), 'nullable', 'exists:finance_categories,id'],
            'account_id' => [Rule::requiredIf(in_array($type, ['income', 'expense', 'adjustment'], true)), 'nullable', 'exists:finance_accounts,id'],
            'from_account_id' => [Rule::requiredIf($type === 'transfer'), 'nullable', 'exists:finance_accounts,id', 'different:to_account_id'],
            'to_account_id' => [Rule::requiredIf($type === 'transfer'), 'nullable', 'exists:finance_accounts,id', 'different:from_account_id'],
            'adjustment_direction' => [Rule::requiredIf($type === 'adjustment'), 'nullable', Rule::in(['increase', 'decrease'])],
            'description' => [Rule::requiredIf($type === 'adjustment'), 'nullable', 'string', 'max:5000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (in_array($this->type, ['income', 'expense'], true) && $this->category_id) {
                $matches = FinanceCategory::query()->whereKey($this->category_id)->where('type', $this->type)->where('is_active', true)->exists();
                if (! $matches) {
                    $validator->errors()->add('category_id', 'The category must be an active category matching the transaction type.');
                }
            }

            $accountIds = match ($this->type) {
                'income', 'expense', 'adjustment' => [$this->account_id],
                'transfer' => [$this->from_account_id, $this->to_account_id],
                default => [],
            };

            foreach (array_filter($accountIds) as $accountId) {
                if (! FinanceAccount::query()->whereKey($accountId)->where('is_active', true)->exists()) {
                    $validator->errors()->add('account_id', 'Transactions can only be recorded against active accounts.');
                    break;
                }
            }
        }];
    }
}
