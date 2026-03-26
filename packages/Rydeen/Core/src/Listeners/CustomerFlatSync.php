<?php

namespace Rydeen\Core\Listeners;

use Illuminate\Support\Facades\DB;

class CustomerFlatSync
{
    /**
     * Patch customer_flat with fields the B2B FlatIndexer misses:
     * first_name, last_name.
     *
     * Note: business_name is a custom attribute (not a column on customers),
     * so the B2B FlatIndexer handles it via customer_attribute_values.
     */
    public function afterUpdate($customer): void
    {
        if (! (bool) core()->getConfigData('b2b_suite.general.settings.active')) {
            return;
        }

        DB::table('customer_flat')
            ->where('customer_id', $customer->id)
            ->update([
                'first_name' => $customer->first_name,
                'last_name'  => $customer->last_name,
            ]);
    }
}
