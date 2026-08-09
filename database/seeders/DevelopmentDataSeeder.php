<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            SkillSeeder::class,
            ExperienceSeeder::class,
            EducationSeeder::class,
            TrainingSeeder::class,
            TechnologySeeder::class,
            ProjectSeeder::class,
            CertificateSeeder::class,
            SocialLinkSeeder::class,
            WebsiteSettingSeeder::class,
        ]);
    }
}
