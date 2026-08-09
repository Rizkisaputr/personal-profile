<?php

namespace Database\Seeders;

use App\Models\FinanceDebt;
use App\Models\FinanceRecurringBill;
use Illuminate\Database\Seeder;

class DashboardNotificationDummySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $debt = FinanceDebt::updateOrCreate(
            ['name' => 'Shopee PayLater'],
            [
                'type' => 'debt',
                'party_name' => 'Shopee',
                'original_amount' => '3000000.00',
                'status' => 'active',
            ],
        );

        $debt->schedules()->updateOrCreate(
            ['notes' => 'Dummy untuk pengujian notifikasi dashboard.'],
            [
                'amount' => '750000.00',
                'due_date' => today()->addDays(5),
                'status' => 'upcoming',
            ],
        );

        FinanceRecurringBill::updateOrCreate(
            ['name' => 'Internet Rumah'],
            [
                'amount' => '450000.00',
                'frequency' => 'monthly',
                'due_day' => today()->subDays(2)->day,
                'next_due_date' => today()->subDays(2),
                'is_active' => true,
                'notes' => 'Dummy overdue untuk pengujian notifikasi dashboard.',
            ],
        );
    }
}
