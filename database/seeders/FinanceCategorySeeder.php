<?php

namespace Database\Seeders;

use App\Models\FinanceCategory;
use Illuminate\Database\Seeder;

class FinanceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'income' => ['Salary', 'Project', 'Business', 'Bonus', 'Other'],
            'expense' => ['Food', 'Transport', 'Shopping', 'Bills', 'Subscription', 'Entertainment', 'Education', 'Health', 'Debt Payment', 'Other'],
        ];

        foreach ($categories as $type => $names) {
            foreach ($names as $index => $name) {
                FinanceCategory::updateOrCreate(
                    ['name' => $name, 'type' => $type],
                    ['is_active' => true, 'sort_order' => $index],
                );
            }
        }
    }
}
