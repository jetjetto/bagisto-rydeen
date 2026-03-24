<?php

namespace Rydeen\Pricing\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Rydeen\Pricing\Models\Promotion;
use Rydeen\Pricing\Models\PromotionItem;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     */
    public function index(): View
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->get();

        return view('rydeen-pricing::admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create(): View
    {
        return view('rydeen-pricing::admin.promotions.create');
    }

    /**
     * Store a newly created promotion.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percentage,threshold,timing,sku_level',
            'value'      => 'required|numeric|min:0',
            'min_qty'    => 'nullable|integer|min:1',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'scope'      => 'required|in:all,category,customer_group,sku',
            'scope_id'   => 'nullable|integer',
            'active'     => 'sometimes|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $promotion = Promotion::create($validated);

        // Handle SKU-level promotion items
        if ($request->has('items')) {
            foreach ($request->input('items', []) as $item) {
                if (! empty($item['product_id'])) {
                    PromotionItem::create([
                        'promotion_id'  => $promotion->id,
                        'product_id'    => $item['product_id'],
                        'override_price' => $item['override_price'] ?? null,
                    ]);
                }
            }
        }

        session()->flash('success', trans('rydeen-pricing::app.saved'));

        return redirect()->route('admin.rydeen.promotions.index');
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(int $id): View
    {
        $promotion = Promotion::with('items')->findOrFail($id);

        return view('rydeen-pricing::admin.promotions.create', compact('promotion'));
    }

    /**
     * Update the specified promotion.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:percentage,threshold,timing,sku_level',
            'value'      => 'required|numeric|min:0',
            'min_qty'    => 'nullable|integer|min:1',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'scope'      => 'required|in:all,category,customer_group,sku',
            'scope_id'   => 'nullable|integer',
            'active'     => 'sometimes|boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $promotion->update($validated);

        // Sync SKU-level promotion items
        if ($request->has('items')) {
            $promotion->items()->delete();

            foreach ($request->input('items', []) as $item) {
                if (! empty($item['product_id'])) {
                    PromotionItem::create([
                        'promotion_id'  => $promotion->id,
                        'product_id'    => $item['product_id'],
                        'override_price' => $item['override_price'] ?? null,
                    ]);
                }
            }
        }

        session()->flash('success', trans('rydeen-pricing::app.updated'));

        return redirect()->route('admin.rydeen.promotions.index');
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(int $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        $promotion->delete();

        return new JsonResponse([
            'message' => trans('rydeen-pricing::app.deleted'),
        ]);
    }
}
