<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rydeen\Dealer\Models\ResourceItem;

class ResourcesController extends Controller
{
    /**
     * Show resources/FAQ page grouped by category.
     */
    public function index(Request $request)
    {
        $query = ResourceItem::active()->orderBy('sort_order');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        $items = $query->get();
        $grouped = $items->groupBy('category');

        return view('rydeen-dealer::shop.resources.index', compact('grouped'));
    }
}
