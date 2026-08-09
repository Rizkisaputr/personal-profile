<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    public function run(): void
    {
        SocialLink::whereIn('url', [
            'https://github.com/example',
            'https://linkedin.com/in/example',
            'https://instagram.com/example',
            'mailto:raka@example.com',
            'https://wa.me/6281234567890',
        ])->delete();
    }
}
