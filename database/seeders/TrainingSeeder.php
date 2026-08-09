<?php

namespace Database\Seeders;

use App\Models\Training;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        Training::updateOrCreate(
            [
                'institution' => 'Kampung Inggris Pare',
                'program_name' => 'English Language Program',
            ],
            [
                'field_of_study' => 'English Language',
                'location' => 'Pare, Kediri, East Java, Indonesia',
                'start_date' => null,
                'end_date' => null,
                'credential_url' => null,
                'description' => 'Mengikuti program pembelajaran Bahasa Inggris dengan fokus pada speaking, grammar, vocabulary, dan komunikasi sehari-hari.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
