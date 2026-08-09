<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profile = Profile::firstOrCreate(['id' => 1], ['full_name' => 'Rizki Saputra']);

        $profile->update([
            'full_name' => 'Rizki Saputra',
            'professional_title' => 'Full Stack Web Developer',
            'tagline' => 'Building reliable digital solutions for real business needs.',
            'short_bio' => 'Full Stack Web Developer focused on building practical, reliable, and maintainable web applications across backend, frontend, databases, APIs, cloud infrastructure, and deployment.',
            'about' => "I am a Full Stack Web Developer focused on building practical digital systems that solve real business problems.\n\nMy work covers the full application lifecycle, from understanding business requirements and designing database structures to backend development, frontend interfaces, API integration, infrastructure, and deployment.\n\nI enjoy building business applications, internal management systems, dashboards, workflow automation, and cloud-based solutions.\n\nMy approach is simple: technology should make processes easier, data more useful, and systems more reliable.",
            'availability_status' => 'Open to selected projects and collaborations.',
        ]);

        $dummyValues = [
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-08-17',
            'marital_status' => 'Single',
            'email' => 'raka@example.com',
            'phone' => '081234567890',
            'whatsapp' => '6281234567890',
            'location' => 'Indonesia',
        ];

        foreach ($dummyValues as $field => $dummyValue) {
            $currentValue = $field === 'birth_date'
                ? $profile->{$field}?->format('Y-m-d')
                : $profile->{$field};

            if ($currentValue === $dummyValue) {
                $profile->{$field} = null;
            }
        }

        $profile->save();
    }
}
