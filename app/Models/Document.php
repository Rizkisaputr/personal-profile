<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['name', 'category', 'document_number', 'issue_date', 'expiry_date', 'file_path', 'original_filename', 'mime_type', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['issue_date' => 'date', 'expiry_date' => 'date', 'is_active' => 'boolean'];
    }

    public function scopeExpiresWithin(Builder $query, int $days): Builder
    {
        return $query->where('is_active', true)->whereBetween('expiry_date', [today(), today()->addDays($days)]);
    }

    public function scopeExpiresToday(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereDate('expiry_date', today());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereDate('expiry_date', '<', today());
    }

    public function maskedNumber(): ?string
    {
        if (! $this->document_number) {
            return null;
        }
        $visible = mb_substr($this->document_number, -4);

        return str_repeat('•', max(0, mb_strlen($this->document_number) - 4)).$visible;
    }
}
