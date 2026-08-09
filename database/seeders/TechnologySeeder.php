<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        Technology::where('slug', 'vue')->delete();

        $technologies = [
            'Laravel' => 'laravel', 'PHP' => 'php', 'CodeIgniter' => 'codeigniter',
            'Vue.js' => 'vue', 'TypeScript' => 'typescript', 'JavaScript' => 'javascript',
            'Tailwind CSS' => 'tailwind', 'Inertia.js' => 'inertia', 'MySQL' => 'mysql',
            'MariaDB' => 'mysql', 'Docker' => 'docker', 'Nginx' => 'nginx',
            'Linux' => 'linux', 'AWS' => 'aws', 'Cloudflare' => 'cloudflare',
            'Git' => 'git', 'GitLab' => 'gitlab', 'REST API' => 'api',
        ];

        foreach ($technologies as $name => $icon) {
            Technology::updateOrCreate(
                ['slug' => Str::slug($name)],
                compact('name', 'icon'),
            );
        }
    }
}
