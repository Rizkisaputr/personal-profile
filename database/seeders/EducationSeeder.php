<?php

namespace Database\Seeders;

use App\Models\Education;
use Illuminate\Database\Seeder;

class EducationSeeder extends Seeder
{
    public function run(): void
    {
        Education::whereIn('institution', [
            'Example University',
            'Universitas Teknologi Indonesia',
            'Digital Business Academy',
        ])->delete();

        $legacySchool = Education::where('institution', 'SMAN 3 Muara Bungo')->first();

        if ($legacySchool && ! Education::where('institution', 'SMA N 4 Muara Bungo')->exists()) {
            $legacySchool->update(['institution' => 'SMA N 4 Muara Bungo']);
        } elseif ($legacySchool) {
            $legacySchool->delete();
        }

        Education::updateOrCreate(
            ['institution' => 'Universitas Amikom Yogyakarta'],
            [
                'degree' => 'Diploma',
                'field_of_study' => 'Informatics Engineering',
                'location' => 'Yogyakarta, Indonesia',
                'start_year' => 2019,
                'end_year' => 2022,
                'description' => "Diploma of Informatics Engineering.\nGPA: 3.69 / 4.00",
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        Education::updateOrCreate(
            ['institution' => 'SMA N 4 Muara Bungo'],
            [
                'degree' => 'Sekolah Menengah Atas',
                'field_of_study' => 'Science',
                'location' => 'Muara Bungo, Jambi, Indonesia',
                'start_year' => 2016,
                'end_year' => 2019,
                'description' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
