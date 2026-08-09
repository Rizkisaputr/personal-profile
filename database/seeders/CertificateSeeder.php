<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        Certificate::whereIn('credential_id', [
            'fdscvdsvdsvd',
            'LARAVEL-2025-001',
            'CLOUD-2025-002',
            'DB-2024-003',
            'BPA-2024-004',
        ])->delete();

        Certificate::updateOrCreate(
            ['title' => 'Data Analysis', 'issuer' => 'Coursera'],
            [
                'issue_date' => '2023-01-01',
                'credential_id' => null,
                'credential_url' => null,
                'description' => 'Program pembelajaran Data Analysis melalui Coursera.',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        Certificate::updateOrCreate(
            ['title' => 'English Course', 'issuer' => 'Coursera'],
            [
                'issue_date' => '2023-07-01',
                'credential_id' => null,
                'credential_url' => null,
                'description' => 'Program pembelajaran Bahasa Inggris melalui Coursera.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
