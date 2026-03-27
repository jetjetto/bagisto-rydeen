<?php

namespace Rydeen\Dealer\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Rydeen\Pricing\Services\PriceResolver;

class CatalogController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CategoryRepository $categoryRepository,
        protected PriceResolver $priceResolver
    ) {}

    /**
     * Show the product catalog.
     */
    public function index(Request $request)
    {
        $customer = auth('customer')->user();

        $query = $this->productRepository->scopeQuery(function ($q) use ($request) {
            $q->where('status', 1);

            if ($request->category) {
                $q->whereHas('categories', fn ($cq) => $cq->where('categories.id', $request->category));
            }

            if ($request->search) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('sku', 'like', "%{$request->search}%")
                       ->orWhereHas('attribute_values', function ($aq) use ($request) {
                           $aq->where('text_value', 'like', "%{$request->search}%");
                       });
                });
            }

            return $q->orderBy('created_at', 'desc');
        });

        $products = $query->paginate(12);
        $categories = $this->categoryRepository->getVisibleCategoryTree();

        // Resolve prices with PriceResolver
        $prices = [];

        foreach ($products as $product) {
            $msrp = (float) ($product->price ?? 0);
            $groupPrice = $customer->customer_group_id
                ? $this->getGroupPrice($product, $customer->customer_group_id)
                : null;

            $basePrice = $groupPrice ?? $msrp;

            if ($basePrice > 0) {
                $categoryIds = $product->categories->pluck('id')->toArray();
                $resolved = $customer->customer_group_id
                    ? $this->priceResolver->resolve(
                        $product->id,
                        $basePrice,
                        $customer->customer_group_id,
                        1,
                        $categoryIds
                    )
                    : ['price' => $basePrice, 'promo_name' => null];
                $resolved['msrp'] = $msrp;
                $prices[$product->id] = $resolved;
            }
        }

        return view('rydeen-dealer::shop.catalog.index', compact('products', 'categories', 'customer', 'prices'));
    }

    /**
     * Show a single product.
     */
    public function show(string $slug)
    {
        $customer = auth('customer')->user();

        $product = $this->productRepository->findBySlug($slug);

        if (! $product || ! $product->status) {
            abort(404);
        }

        $msrp = (float) ($product->price ?? 0);
        $groupPrice = $customer->customer_group_id
            ? $this->getGroupPrice($product, $customer->customer_group_id)
            : null;

        $basePrice = $groupPrice ?? $msrp;
        $price = null;

        if ($basePrice > 0) {
            $categoryIds = $product->categories->pluck('id')->toArray();
            $price = $customer->customer_group_id
                ? $this->priceResolver->resolve(
                    $product->id,
                    $basePrice,
                    $customer->customer_group_id,
                    1,
                    $categoryIds
                )
                : ['price' => $basePrice, 'promo_name' => null];
            $price['msrp'] = $msrp;
        }

        return view('rydeen-dealer::shop.catalog.product', compact('product', 'customer', 'price'));
    }

    /**
     * Get the group price for a product and customer group.
     */
    protected function getGroupPrice($product, int $customerGroupId): ?float
    {
        $groupPriceEntry = $product->customer_group_prices
            ->where('customer_group_id', $customerGroupId)
            ->first();

        if (! $groupPriceEntry) {
            return null;
        }

        $msrp = $product->price ?? 0;

        return $groupPriceEntry->value_type === 'fixed'
            ? (float) $groupPriceEntry->value
            : $msrp * (1 - $groupPriceEntry->value / 100);
    }
}
