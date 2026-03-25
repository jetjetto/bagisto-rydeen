<?php

namespace Rydeen\Dealer\Shipping;

use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Carriers\AbstractShipping;

class DealerShipping extends AbstractShipping
{
    /**
     * Shipping method carrier code.
     *
     * @var string
     */
    protected $code = 'dealer_shipping';

    /**
     * Shipping method code.
     *
     * @var string
     */
    protected $method = 'dealer_shipping_dealer_shipping';

    /**
     * Calculate rate for dealer shipping (free).
     *
     * @return CartShippingRate|false
     */
    public function calculate()
    {
        if (! $this->isAvailable()) {
            return false;
        }

        return $this->getRate();
    }

    /**
     * Check availability — always active.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Get shipping rate (always $0).
     */
    public function getRate(): CartShippingRate
    {
        $cartShippingRate = new CartShippingRate;

        $cartShippingRate->carrier = $this->getCode();
        $cartShippingRate->carrier_title = 'Dealer Shipping';
        $cartShippingRate->method = $this->getMethod();
        $cartShippingRate->method_title = 'Standard Dealer Shipping';
        $cartShippingRate->method_description = 'Standard dealer shipping — no charge';
        $cartShippingRate->price = 0;
        $cartShippingRate->base_price = 0;

        return $cartShippingRate;
    }
}
