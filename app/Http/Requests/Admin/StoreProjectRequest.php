<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return $this->projectRules();
    }

    protected function projectRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:projects,slug'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'category' => ['nullable', 'string', 'max:255'],
            'project_date' => ['nullable', 'date'],
            'github_url' => ['nullable', 'url', 'max:2048'],
            'demo_url' => ['nullable', 'url', 'max:2048'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'work_status' => ['required', Rule::in(['waiting', 'running', 'on_hold', 'done'])],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'technology_ids' => ['array'],
            'technology_ids.*' => ['integer', 'exists:technologies,id'],
            'gallery_images' => ['array'],
            'gallery_images.*' => ['image', 'max:6144'],
            'gallery_captions' => ['array'],
            'gallery_captions.*' => ['nullable', 'string', 'max:255'],
            'gallery_sort_orders' => ['array'],
            'gallery_sort_orders.*' => ['nullable', 'integer'],
        ];
    }
}
