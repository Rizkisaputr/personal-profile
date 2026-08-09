<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'full_name', 'professional_title', 'tagline', 'short_bio', 'about',
        'profile_image', 'hero_image', 'login_image', 'location', 'email', 'phone', 'whatsapp', 'birth_place',
        'birth_date', 'marital_status', 'availability_status', 'cv_file',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }
}
