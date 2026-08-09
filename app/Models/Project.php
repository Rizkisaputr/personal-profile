<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'short_description', 'description', 'thumbnail', 'category',
        'project_date', 'github_url', 'demo_url', 'client_name', 'is_featured',
        'is_published', 'work_status', 'sort_order', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'project_date' => 'date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function scopeWithWorkStatus(Builder $query, string $status): void
    {
        $query->where('work_status', $status);
    }

    public static function workStatusCounts(): array
    {
        $counts = static::query()->selectRaw('work_status, count(*) as aggregate')->groupBy('work_status')->pluck('aggregate', 'work_status');

        return ['total' => (int) $counts->sum(), 'waiting' => (int) ($counts['waiting'] ?? 0), 'running' => (int) ($counts['running'] ?? 0), 'on_hold' => (int) ($counts['on_hold'] ?? 0), 'done' => (int) ($counts['done'] ?? 0)];
    }
}
