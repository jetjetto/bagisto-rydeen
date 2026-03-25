<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Routing\Controller;

/**
 * ProductController is a thin wrapper that delegates to CatalogController::show().
 * Kept as a separate controller for clarity and potential future expansion.
 */
class ProductController extends Controller
{
    /**
     * Show a single product detail page.
     */
    public function show(string $slug)
    {
        return app(CatalogController::class)->show($slug);
    }
}
