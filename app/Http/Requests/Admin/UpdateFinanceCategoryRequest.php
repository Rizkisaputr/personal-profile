<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinanceCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('finance_categories')
                    ->where('type', $this->input('type'))
                    ->ignore($this->route('category')),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
