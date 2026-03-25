<?php

namespace Rydeen\Dealer\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Rydeen\Dealer\Listeners\OrderListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'checkout.order.save.after' => [
            [OrderListener::class, 'afterOrderCreated'],
        ],
    ];
}
