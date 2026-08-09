<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'title', 'issuer', 'issue_date', 'credential_id', 'credential_url',
        'image', 'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['issue_date' => 'date', 'is_active' => 'boolean'];
    }
}
