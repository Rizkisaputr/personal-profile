<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Document;
use App\Models\FinanceAccount;
use App\Models\FinanceCategory;
use App\Models\FinanceDebt;
use App\Models\FinanceDebtPayment;
use App\Models\FinanceRecurringBill;
use App\Models\FinanceSavingsEntry;
use App\Models\FinanceSavingsGoal;
use App\Models\FinanceSavingsMonthlyTarget;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PersonalDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $accounts = $this->accounts();
        $this->transactions($accounts);
        $this->debts($accounts);
        $this->savings($accounts);
        $this->bills($accounts);
        $this->subscriptions($accounts);
        $this->documents();
        $this->assets();
        $this->projectStatuses();
    }

    private function accounts(): array
    {
        $definitions = [
            'BCA' => ['bank', '15000000.00'], 'BRI' => ['bank', '5000000.00'], 'Cash' => ['cash', '1500000.00'],
            'GoPay' => ['e_wallet', '750000.00'], 'DANA' => ['e_wallet', '500000.00'],
        ];

        return collect($definitions)->mapWithKeys(function (array $data, string $name) {
            $account = FinanceAccount::updateOrCreate(['name' => $name], [
                'type' => $data[0], 'opening_balance' => $data[1], 'currency' => 'IDR', 'is_active' => true,
                'notes' => 'Deterministic development account.',
            ]);

            return [$name => $account];
        })->all();
    }

    private function transactions(array $accounts): void
    {
        $monthly = [
            ['Salary', 'income', 9500000], ['Project', 'income', 2500000], ['Food', 'expense', 900000],
            ['Transport', 'expense', 450000], ['Bills', 'expense', 800000], ['Subscription', 'expense', 450000],
            ['Entertainment', 'expense', 300000],
        ];

        foreach (range(5, 0) as $monthsAgo) {
            $month = now()->subMonthsNoOverflow($monthsAgo);
            foreach ($monthly as $index => [$categoryName, $type, $baseAmount]) {
                $amount = $baseAmount + (($monthsAgo + $index) % 3) * 100000;
                $category = FinanceCategory::where(['name' => $categoryName, 'type' => $type])->firstOrFail();
                FinanceTransaction::updateOrCreate(
                    ['description' => "Development {$month->format('Y-m')} {$categoryName}"],
                    ['type' => $type, 'transaction_date' => $month->copy()->day(min(5 + $index * 3, 27)), 'amount' => $amount, 'category_id' => $category->id,
                        'account_id' => $accounts['BCA']->id, 'reference_type' => 'development_seed'],
                );
            }
        }

        $this->ledger('Development transfer BCA to GoPay', ['type' => 'transfer', 'transaction_date' => today()->subDays(4), 'amount' => '500000.00', 'from_account_id' => $accounts['BCA']->id, 'to_account_id' => $accounts['GoPay']->id]);
        $this->ledger('Development transfer BCA to Cash', ['type' => 'transfer', 'transaction_date' => today()->subDays(2), 'amount' => '1000000.00', 'from_account_id' => $accounts['BCA']->id, 'to_account_id' => $accounts['Cash']->id]);
        $this->ledger('Development balance reconciliation example.', ['type' => 'adjustment', 'transaction_date' => today()->subDay(), 'amount' => '50000.00', 'account_id' => $accounts['Cash']->id, 'adjustment_direction' => 'decrease']);
    }

    private function debts(array $accounts): void
    {
        $definitions = [
            ['Shopee PayLater', 'debt', 'Development Commerce', '5000000.00', '2000000.00', '750000.00', 6],
            ['Cicilan Laptop', 'debt', 'Development Retail', '12000000.00', '6000000.00', '1500000.00', 18],
            ['Personal Loan Example', 'debt', 'Personal Example', '3000000.00', '2000000.00', '1000000.00', -3],
            ['Project Payment — Client A', 'receivable', 'Client A', '4000000.00', '2000000.00', '2000000.00', 10],
            ['Personal Receivable Example', 'receivable', 'Personal Example', '1500000.00', '0.00', '1500000.00', 25],
        ];

        foreach ($definitions as [$name, $type, $party, $original, $paid, $installment, $dueDays]) {
            $debt = FinanceDebt::updateOrCreate(['name' => $name, 'type' => $type], [
                'party_name' => $party, 'original_amount' => $original, 'start_date' => today()->subMonths(4),
                'status' => 'active', 'notes' => 'Development example.',
            ]);
            $schedule = $debt->schedules()->updateOrCreate(['notes' => 'Development next installment'], [
                'due_date' => today()->addDays($dueDays), 'amount' => $installment, 'status' => 'upcoming',
            ]);

            if ((float) $paid > 0) {
                $category = FinanceCategory::where(['name' => $type === 'debt' ? 'Debt Payment' : 'Other', 'type' => $type === 'debt' ? 'expense' : 'income'])->firstOrFail();
                $transaction = $this->ledger("Development payment {$name}", [
                    'type' => $type === 'debt' ? 'expense' : 'income', 'transaction_date' => today()->subMonth(), 'amount' => $paid,
                    'category_id' => $category->id, 'account_id' => $accounts['BCA']->id, 'reference_type' => 'finance_debt', 'reference_id' => $debt->id,
                ]);
                FinanceDebtPayment::updateOrCreate(['debt_id' => $debt->id, 'notes' => 'Development historical payment'], [
                    'schedule_id' => null, 'amount' => $paid, 'payment_date' => today()->subMonth(), 'finance_transaction_id' => $transaction->id,
                ]);
            }
        }
    }

    private function savings(array $accounts): void
    {
        $emergency = FinanceSavingsGoal::updateOrCreate(['name' => 'Dana Darurat'], [
            'target_amount' => '30000000.00', 'start_date' => today()->subMonths(3), 'target_date' => now()->endOfYear(), 'status' => 'active', 'notes' => 'Development savings goal.',
        ]);
        $upgrade = FinanceSavingsGoal::updateOrCreate(['name' => 'Upgrade Laptop'], [
            'target_amount' => '20000000.00', 'start_date' => today()->subMonths(2), 'target_date' => today()->addMonths(8), 'status' => 'active', 'notes' => 'Development savings goal.',
        ]);
        $previous = now()->subMonthNoOverflow();
        foreach ([[$emergency, $previous->year, $previous->month, '5000000.00'], [$emergency, now()->year, now()->month, '5000000.00'], [$upgrade, now()->year, now()->month, '2500000.00']] as [$goal, $year, $month, $target]) {
            FinanceSavingsMonthlyTarget::updateOrCreate(['savings_goal_id' => $goal->id, 'year' => $year, 'month' => $month], ['target_amount' => $target]);
        }

        $this->savingEntry($emergency, $accounts['BCA'], '10750000.00', today()->subMonths(2), 'Development Dana Darurat opening contributions');
        $this->savingEntry($emergency, $accounts['BCA'], '4000000.00', $previous->copy()->day(12), 'Development Dana Darurat previous month');
        $this->savingEntry($emergency, $accounts['BCA'], '3250000.00', today()->subDays(3), 'Development Dana Darurat current month');
        $this->savingEntry($upgrade, $accounts['BRI'], '5000000.00', today()->subDays(5), 'Development Upgrade Laptop contribution');
    }

    private function savingEntry(FinanceSavingsGoal $goal, FinanceAccount $account, string $amount, $date, string $description): void
    {
        $transaction = $this->ledger($description, ['type' => 'adjustment', 'transaction_date' => $date, 'amount' => $amount, 'account_id' => $account->id, 'adjustment_direction' => 'decrease', 'reference_type' => 'finance_savings', 'reference_id' => $goal->id]);
        FinanceSavingsEntry::updateOrCreate(['savings_goal_id' => $goal->id, 'notes' => $description], [
            'type' => 'contribution', 'amount' => $amount, 'entry_date' => $date, 'account_id' => $account->id, 'finance_transaction_id' => $transaction->id,
        ]);
    }

    private function bills(array $accounts): void
    {
        foreach ([['Internet Rumah', '450000.00', 'monthly', 4], ['Listrik', '600000.00', 'monthly', 12], ['VPS', '250000.00', 'monthly', 22], ['Domain Portfolio', '200000.00', 'yearly', 40]] as [$name, $amount, $frequency, $days]) {
            FinanceRecurringBill::updateOrCreate(['name' => $name], ['amount' => $amount, 'frequency' => $frequency, 'due_day' => today()->addDays($days)->day, 'next_due_date' => today()->addDays($days), 'default_account_id' => $accounts['BCA']->id, 'is_active' => true, 'notes' => 'Development recurring bill.']);
        }
    }

    private function subscriptions(array $accounts): void
    {
        foreach ([['ChatGPT', '350000.00', 'monthly', 3], ['Cloud Hosting', '300000.00', 'monthly', 9], ['Domain Service', '200000.00', 'yearly', 45], ['Spotify', '60000.00', 'monthly', 16]] as [$name, $cost, $cycle, $days]) {
            Subscription::updateOrCreate(['service_name' => $name], ['cost' => $cost, 'billing_cycle' => $cycle, 'start_date' => today()->subMonths(3), 'next_renewal_date' => today()->addDays($days), 'payment_account_id' => $accounts['BCA']->id, 'is_active' => true, 'notes' => 'Development subscription.']);
        }
    }

    private function documents(): void
    {
        foreach ([['SIM A', 'Identity', 24], ['Paspor', 'Identity', 180], ['Ijazah Universitas', 'Education', null], ['STNK Motor', 'Vehicle', 6], ['Employment Contract', 'Employment', null]] as [$name, $category, $days]) {
            Document::updateOrCreate(['name' => $name], ['category' => $category, 'document_number' => null, 'issue_date' => null, 'expiry_date' => $days === null ? null : today()->addDays($days), 'file_path' => null, 'original_filename' => null, 'mime_type' => null, 'notes' => 'Development metadata only; no private file attached.', 'is_active' => true]);
        }
    }

    private function assets(): void
    {
        foreach ([['Laptop Development', 'Computer', '18000000.00', today()->subYear(), today()->addDays(90)], ['Smartphone', 'Phone', '6000000.00', today()->subMonths(8), today()->addDays(35)], ['Motor', 'Vehicle', '25000000.00', today()->subYears(2), null], ['Monitor', 'Electronics', '3000000.00', today()->subMonths(10), null]] as [$name, $category, $price, $purchase, $warranty]) {
            Asset::updateOrCreate(['name' => $name], ['category' => $category, 'purchase_price' => $price, 'purchase_date' => $purchase, 'warranty_expiry' => $warranty, 'serial_number' => null, 'status' => 'active', 'notes' => 'Development asset.']);
        }
    }

    private function projectStatuses(): void
    {
        $statuses = ['waiting', 'running', 'running', 'on_hold'];
        Project::orderBy('sort_order')->orderBy('id')->get()->each(function (Project $project, int $index) use ($statuses) {
            $project->update(['work_status' => $statuses[$index] ?? 'done']);
        });
    }

    private function ledger(string $description, array $attributes): FinanceTransaction
    {
        return FinanceTransaction::updateOrCreate(['description' => $description], $attributes + ['description' => $description, 'reference_type' => 'development_seed']);
    }
}
