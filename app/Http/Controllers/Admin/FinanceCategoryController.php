<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinanceCategoryRequest;
use App\Http\Requests\Admin\UpdateFinanceCategoryRequest;
use App\Models\FinanceCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FinanceCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/finance/categories/Index', [
            'categories' => FinanceCategory::query()
                ->orderBy('type')
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(25),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/finance/categories/Create');
    }

    public function store(StoreFinanceCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = ((int) FinanceCategory::where('type', $data['type'])->max('sort_order')) + 1;
        FinanceCategory::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Finance category created.']);

        return to_route('admin.finance.categories.index');
    }

    public function edit(FinanceCategory $category): Response
    {
        return Inertia::render('admin/finance/categories/Edit', ['category' => $category]);
    }

    public function update(UpdateFinanceCategoryRequest $request, FinanceCategory $category): RedirectResponse
    {
        $data = $request->validated();

        if ($category->type !== $data['type']) {
            $data['sort_order'] = ((int) FinanceCategory::where('type', $data['type'])->max('sort_order')) + 1;
        }

        $category->update($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Finance category updated.']);

        return to_route('admin.finance.categories.index');
    }

    public function destroy(FinanceCategory $category): RedirectResponse
    {
        if ($category->isInUse()) {
            $category->update(['is_active' => false]);
            $message = 'Category deactivated because it is in use.';
        } else {
            $category->delete();
            $message = 'Finance category deleted.';
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('admin.finance.categories.index');
    }
}
