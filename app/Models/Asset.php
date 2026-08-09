<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = ['name', 'category', 'purchase_price', 'purchase_date', 'warranty_expiry', 'serial_number', 'status', 'notes'];

    protected function casts(): array
    {
        return ['purchase_price' => 'decimal:2', 'purchase_date' => 'date', 'warranty_expiry' => 'date'];
    }
}
