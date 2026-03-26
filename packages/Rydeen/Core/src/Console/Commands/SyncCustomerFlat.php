<?php

namespace Rydeen\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCustomerFlat extends Command
{
    protected $signature = 'rydeen:sync-customer-flat';

    protected $description = 'Backfill first_name, last_name into customer_flat from customers table, and business_name from customer_attribute_values';

    public function handle(): int
    {
        $updated = DB::update('
            UPDATE customer_flat
            JOIN customers ON customers.id = customer_flat.customer_id
            SET customer_flat.first_name = customers.first_name,
                customer_flat.last_name  = customers.last_name
        ');

        $this->info("Updated {$updated} customer_flat rows with first_name/last_name.");

        // business_name comes from custom attributes (company_attribute_values)
        $businessAttr = DB::table('company_attributes')->where('code', 'business_name')->first();

        if ($businessAttr) {
            $bizUpdated = DB::update('
                UPDATE customer_flat
                JOIN customer_attribute_values ON customer_attribute_values.customer_id = customer_flat.customer_id
                    AND customer_attribute_values.company_attribute_id = ?
                    AND (customer_attribute_values.locale = customer_flat.locale OR customer_attribute_values.locale IS NULL)
                SET customer_flat.business_name = customer_attribute_values.text_value
            ', [$businessAttr->id]);

            $this->info("Updated {$bizUpdated} customer_flat rows with business_name.");
        } else {
            $this->warn('No business_name custom attribute found — skipping.');
        }

        return self::SUCCESS;
    }
}
