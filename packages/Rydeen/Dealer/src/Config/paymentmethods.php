<?php

return [
    'dealer_order' => [
        'code'        => 'dealer_order',
        'title'       => 'Dealer Order',
        'description' => 'No payment required',
        'class'       => \Rydeen\Dealer\Payment\DealerOrder::class,
        'active'      => true,
        'sort'        => 1,
    ],
];
