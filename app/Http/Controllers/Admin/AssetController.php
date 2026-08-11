<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    private const CATEGORIES = ['Computer', 'Phone', 'Vehicle', 'Electronics', 'Property', 'Digital', 'Other'];

    private const STATUSES = ['active', 'sold', 'lost', 'disposed'];

    public function index(): Response
    {
        return Inertia::render('admin/assets/Index', ['assets' => Asset::latest()->get()]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/assets/Form', ['categories' => self::CATEGORIES]);
    }

    public function edit(Asset $asset): Response
    {
        return Inertia::render('admin/assets/Form', ['asset' => $asset, 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request): RedirectResponse
    {
        Asset::create($this->validated($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asset added successfully.']);

        return to_route('admin.assets.index');
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $asset->update($this->validated($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asset updated successfully.']);

        return to_route('admin.assets.index');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asset deleted successfully.']);

        return back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['required', Rule::in(self::CATEGORIES)],
            'purchase_price' => ['nullable', 'decimal:0,2', 'gte:0'], 'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'], 'serial_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(self::STATUSES)], 'notes' => ['nullable', 'string'],
        ]);
    }
}
