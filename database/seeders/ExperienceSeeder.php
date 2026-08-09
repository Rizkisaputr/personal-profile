<?php

namespace Database\Seeders;

use App\Models\Experience;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        Experience::whereIn('company_name', [
            'Example Technology Company',
            'Example Business',
            'PT Nusantara Digital Systems',
            'PT Kreatif Teknologi Indonesia',
        ])->delete();

        $experiences = [
            [
                'position' => 'Fullstack Web Developer',
                'company_name' => 'Dewi Media Lestari',
                'employment_type' => 'Fulltime',
                'location' => 'Yogyakarta',
                'start_date' => '2023-09-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Bekerja sebagai Fullstack Web Developer dalam pengembangan dan pemeliharaan sistem internal perusahaan, mencakup pengembangan backend, frontend, database, integrasi sistem, serta peningkatan proses bisnis berbasis web.',
            ],
            [
                'position' => 'Freelance Fullstack Web Developer',
                'company_name' => 'Freelance',
                'employment_type' => 'Project Based',
                'location' => null,
                'start_date' => '2022-12-01',
                'end_date' => '2023-09-01',
                'is_current' => false,
                'description' => 'Mengerjakan pengembangan aplikasi dan website berbasis proyek, mulai dari perancangan kebutuhan, pengembangan frontend dan backend, hingga integrasi database dan penyelesaian fitur sesuai kebutuhan project.',
            ],
            [
                'position' => 'Freelance Fullstack Web Developer',
                'company_name' => 'Dewi Media Lestari',
                'employment_type' => 'Freelance',
                'location' => 'Yogyakarta',
                'start_date' => '2022-08-01',
                'end_date' => '2022-12-01',
                'is_current' => false,
                'description' => 'Mendukung pengembangan sistem internal Dewi Media Lestari secara freelance dengan fokus pada implementasi fitur web, pengelolaan database, dan kebutuhan operasional perusahaan.',
            ],
            [
                'position' => 'Practicum Assistant — Perancangan Web',
                'company_name' => 'Universitas Amikom Yogyakarta',
                'employment_type' => 'Part-time / Academic',
                'location' => null,
                'start_date' => '2021-09-01',
                'end_date' => '2022-01-01',
                'is_current' => false,
                'description' => 'Mendampingi kegiatan praktikum Perancangan Web, membantu mahasiswa memahami struktur website, implementasi frontend dasar, serta proses pengembangan aplikasi web.',
            ],
            [
                'position' => 'Web Development Intern',
                'company_name' => 'Dewi Media Lestari',
                'employment_type' => 'Internship',
                'location' => 'Yogyakarta',
                'start_date' => '2021-09-01',
                'end_date' => '2022-01-01',
                'is_current' => false,
                'description' => 'Menjalani internship pada bidang Web Development dengan fokus pada pembelajaran dan implementasi aplikasi web untuk kebutuhan internal perusahaan.',
            ],
        ];

        foreach ($experiences as $sortOrder => $experience) {
            Experience::updateOrCreate(
                [
                    'company_name' => $experience['company_name'],
                    'position' => $experience['position'],
                    'start_date' => $experience['start_date'],
                ],
                [...$experience, 'sort_order' => $sortOrder, 'is_active' => true],
            );
        }
    }
}
