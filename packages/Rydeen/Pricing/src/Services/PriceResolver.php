<?php

namespace Rydeen\Pricing\Services;

use Rydeen\Pricing\Models\Promotion;

class PriceResolver
{
    /**
     * Resolve the best price for a product given the dealer's group price.
     *
     * @return array{price: float, promo_name: string|null}
     */
    public function resolve(
        int $productId,
        float $groupPrice,
        int $customerGroupId,
        int $qty = 1,
        array $categoryIds = []
    ): array {
        $promotions = Promotion::where('active', true)
            ->where(function ($q) use ($customerGroupId, $categoryIds, $productId) {
                $q->where('scope', 'all')
                    ->orWhere(function ($q) use ($customerGroupId) {
                        $q->where('scope', 'customer_group')
                            ->where('scope_id', $customerGroupId);
                    })
                    ->orWhere(function ($q) use ($categoryIds) {
                        $q->where('scope', 'category')
                            ->whereIn('scope_id', $categoryIds);
                    })
                    ->orWhere(function ($q) use ($productId) {
                        $q->where('scope', 'sku')
                            ->where('scope_id', $productId);
                    });
            })
            ->get();

        $bestPrice = $groupPrice;
        $bestPromoName = null;

        foreach ($promotions as $promo) {
            $candidatePrice = $this->calculatePrice($promo, $productId, $groupPrice, $qty);

            if ($candidatePrice !== null && $candidatePrice < $bestPrice) {
                $bestPrice = $candidatePrice;
                $bestPromoName = $promo->name;
            }
        }

        return [
            'price'      => round($bestPrice, 2),
            'promo_name' => $bestPromoName,
        ];
    }

    /**
     * Calculate the price for a product given a specific promotion.
     */
    protected function calculatePrice(Promotion $promo, int $productId, float $groupPrice, int $qty): ?float
    {
        return match ($promo->type) {
            'percentage' => $groupPrice * (1 - $promo->value / 100),

            'threshold' => $qty >= ($promo->min_qty ?? 1)
                ? $groupPrice * (1 - $promo->value / 100)
                : null,

            'timing' => $this->isWithinDateRange($promo)
                ? $groupPrice * (1 - $promo->value / 100)
                : null,

            'sku_level' => $this->getSkuOverridePrice($promo, $productId),

            default => null,
        };
    }

    /**
     * Check if the current time is within the promotion's date range.
     */
    protected function isWithinDateRange(Promotion $promo): bool
    {
        $now = now();

        if ($promo->starts_at && $now->lt($promo->starts_at)) {
            return false;
        }

        if ($promo->ends_at && $now->gt($promo->ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get the SKU-level override price from promotion items.
     */
    protected function getSkuOverridePrice(Promotion $promo, int $productId): ?float
    {
        $item = $promo->items()->where('product_id', $productId)->first();

        return $item?->override_price;
    }
}
