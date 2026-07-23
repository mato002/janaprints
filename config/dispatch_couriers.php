<?php

/**
 * Courier options and dispatch workflow metadata.
 */
return [

    'couriers' => [
        'in_house' => 'In-house delivery',
        'fargo' => 'Fargo Courier',
        'g4s' => 'G4S',
        'pickup' => 'Customer collection',
        'other' => 'Other courier',
    ],

    'integration_keys' => [
        'fargo' => 'fargo_courier',
        'g4s' => 'g4s',
    ],

    'courier_profiles' => [
        'fargo' => [
            'contact' => 'Fargo Dispatch · 020 123 4567',
            'sla' => 'Same-day / next-day within Nairobi',
            'tracking_url' => 'https://www.fargocourier.com/track/{tracking}',
        ],
        'g4s' => [
            'contact' => 'G4S Secure Logistics · 020 234 5678',
            'sla' => '24–48 hours nationwide',
            'tracking_url' => 'https://www.g4s.com/en-ke/track/{tracking}',
        ],
    ],

    'delivery_routes' => [
        'westlands' => 'Westlands route',
        'cbd' => 'CBD route',
        'industrial' => 'Industrial area route',
        'outbound' => 'Outbound route',
    ],

];
