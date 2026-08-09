<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Backend' => ['Laravel' => 90, 'PHP' => 88, 'CodeIgniter' => 80, 'REST API' => 86],
            'Frontend' => ['Vue.js' => 82, 'TypeScript' => 78, 'JavaScript' => 84, 'Tailwind CSS' => 82, 'Inertia.js' => 80],
            'Database' => ['MySQL' => 88, 'MariaDB' => 84, 'Database Design' => 86, 'SQL' => 84],
            'Infrastructure' => ['Docker' => 75, 'Nginx' => 76, 'Linux' => 78, 'AWS' => 70, 'Cloudflare' => 80],
            'Engineering' => ['Git' => 86, 'GitLab' => 78, 'API Integration' => 84, 'Application Deployment' => 78],
            'Business Systems' => ['System Analysis' => 88, 'Business Process Analysis' => 86, 'Workflow Design' => 84, 'Dashboard Development' => 82, 'Internal Business Applications' => 86],
        ];

        $sortOrder = 0;

        foreach ($groups as $category => $skills) {
            foreach ($skills as $name => $proficiency) {
                Skill::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    compact('name', 'category', 'proficiency') + [
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ],
                );
            }
        }

        $portfolioSkills = [
            'Professional / Technical' => [
                'Design System',
                'UI/UX Design',
                'PHP',
                'CodeIgniter',
                'SQL',
            ],
            'Software / Tools' => [
                'Figma',
                'Visual Studio Code',
                'SQLyog',
                'GitLab',
            ],
        ];

        foreach ($portfolioSkills as $category => $skills) {
            foreach ($skills as $name) {
                $skill = Skill::firstOrNew(['slug' => Str::slug($name)]);
                $skill->name = $name;
                $skill->category = $category;
                $skill->is_active = true;

                if (! $skill->exists) {
                    $skill->sort_order = $sortOrder++;
                }

                $skill->save();
            }
        }

        Skill::whereIn('slug', ['sql-optimization', 'vite', 'accounting-systems', 'operational-workflow-design'])->delete();
    }
}
