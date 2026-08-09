<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustFinanceBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'actual_balance' => ['required', 'decimal:0,2', 'min:-9999999999999999.99', 'max:9999999999999999.99'],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }
}
