<?php

namespace Rydeen\Dealer\Payment;

use Webkul\Payment\Payment\Payment;

class DealerOrder extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'dealer_order';

    /**
     * Get redirect url.
     *
     * @return string|false
     */
    public function getRedirectUrl()
    {
        return false;
    }

    /**
     * Get payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Dealer Order';
    }

    /**
     * Get payment method description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'No payment required for dealer orders.';
    }

    /**
     * Is available — always active for dealer portal.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }
}
