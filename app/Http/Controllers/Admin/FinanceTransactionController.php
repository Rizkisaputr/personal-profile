<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinanceTransactionRequest;
use App\Http\Requests\Admin\UpdateFinanceTransactionRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceBillPayment;
use App\Models\FinanceCategory;
use App\Models\FinanceDebtPayment;
use App\Models\FinanceSavingsEntry;
use App\Models\FinanceTransaction;
use App\Models\SubscriptionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FinanceTransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $transactions = FinanceTransaction::with(['category:id,name', 'account:id,name', 'fromAccount:id,name', 'toAccount:id,name'])
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->filled('account'), fn ($q) => $q->where(fn ($accounts) => $accounts->where('account_id', $request->account)->orWhere('from_account_id', $request->account)->orWhere('to_account_id', $request->account)))
            ->when($request->filled('search'), fn ($q) => $q->where('description', 'like', '%'.$request->search.'%'))
            ->orderByDesc('transaction_date')->orderByDesc('id')->paginate(25)->withQueryString();

        return Inertia::render('admin/finance/transactions/Index', [
            'transactions' => $transactions, 'filters' => $request->only(['date_from', 'date_to', 'type', 'account', 'category', 'search']),
            'accounts' => FinanceAccount::orderBy('name')->get(['id', 'name']), 'categories' => FinanceCategory::orderBy('type')->orderBy('sort_order')->get(['id', 'name', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/transactions/Create', [
            'accounts' => FinanceAccount::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']),
            'categories' => FinanceCategory::where('is_active', true)->orderBy('type')->orderBy('sort_order')->get(['id', 'name', 'type']),
        ]);
    }

    public function store(StoreFinanceTransactionRequest $request): RedirectResponse
    {
        $data = $this->normalize($request->validated());

        DB::transaction(fn () => FinanceTransaction::create($data));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transaction recorded.']);

        return to_route('admin.finance.transactions.index');
    }

    public function edit(FinanceTransaction $transaction): Response
    {
        return Inertia::render('admin/finance/transactions/Create', [
            'transaction' => $transaction,
            'accounts' => FinanceAccount::where('is_active', true)->orWhereIn('id', array_filter([$transaction->account_id, $transaction->from_account_id, $transaction->to_account_id]))->orderBy('name')->get(['id', 'name', 'type']),
            'categories' => FinanceCategory::where('is_active', true)->orWhere('id', $transaction->category_id)->orderBy('type')->orderBy('sort_order')->get(['id', 'name', 'type']),
        ]);
    }

    public function update(UpdateFinanceTransactionRequest $request, FinanceTransaction $transaction): RedirectResponse
    {
        DB::transaction(fn () => $transaction->update($this->normalize($request->validated())));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transaction updated.']);

        return to_route('admin.finance.transactions.index');
    }

    public function destroy(FinanceTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $debtPayment = FinanceDebtPayment::with(['debt', 'schedule'])
                ->where('finance_transaction_id', $transaction->id)
                ->first();

            $debtPayment?->delete();
            FinanceSavingsEntry::where('finance_transaction_id', $transaction->id)->delete();
            FinanceBillPayment::where('finance_transaction_id', $transaction->id)->delete();
            SubscriptionPayment::where('finance_transaction_id', $transaction->id)->delete();
            $transaction->delete();

            if ($debtPayment?->schedule) {
                $debtPayment->schedule->update(['status' => 'upcoming']);
            }

            if ($debtPayment?->debt?->status === 'paid') {
                $debtPayment->debt->update(['status' => 'active']);
            }
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transaction deleted.']);

        return back();
    }

    private function normalize(array $data): array
    {
        if ($data['type'] === 'transfer') {
            $data['account_id'] = $data['category_id'] = $data['adjustment_direction'] = null;
        } elseif (in_array($data['type'], ['income', 'expense'], true)) {
            $data['from_account_id'] = $data['to_account_id'] = $data['adjustment_direction'] = null;
        } else {
            $data['category_id'] = $data['from_account_id'] = $data['to_account_id'] = null;
        }

        return $data;
    }
}
