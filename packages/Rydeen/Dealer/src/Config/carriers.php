<?php

return [
    'dealer_shipping' => [
        'code'         => 'dealer_shipping',
        'title'        => 'Dealer Shipping',
        'description'  => 'Standard dealer shipping',
        'active'       => true,
        'default_rate' => 0,
        'type'         => 'per_unit',
        'class'        => \Rydeen\Dealer\Shipping\DealerShipping::class,
    ],
];
