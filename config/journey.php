<?php

$i = require __DIR__ . '/public-images.php';

return [

    'visual_journey' => [
        ['label' => 'Artwork', 'image' => $i['artwork'], 'alt' => 'Artwork and design preparation'],
        ['label' => 'Printing', 'image' => $i['print_press'], 'alt' => 'Commercial printing production'],
        ['label' => 'Finishing', 'image' => $i['finishing'], 'alt' => 'Print finishing and trimming'],
        ['label' => 'Packaging', 'image' => $i['packaging'], 'alt' => 'Product packaging and wrapping'],
        ['label' => 'Delivery', 'image' => $i['delivery'], 'alt' => 'Delivery and package handover'],
    ],

    'steps' => [
        [
            'number' => 1,
            'slug' => 'request-quote',
            'icon' => 'document',
            'title' => 'Request A Quote',
            'description' => 'Share your requirements, artwork, dimensions, quantities and timelines.',
            'image' => $i['stationery'],
            'alt' => 'Quote request and project brief paperwork',
            'badge' => null,
            'trust' => null,
            'highlight' => false,
        ],
        [
            'number' => 2,
            'slug' => 'artwork-review',
            'icon' => 'design',
            'title' => 'Artwork Review',
            'description' => 'Our design team reviews your artwork for quality, sizing, resolution and print readiness.',
            'image' => $i['artwork'],
            'alt' => 'Design team reviewing artwork on screen',
            'badge' => 'Free Artwork Checks',
            'trust' => null,
            'highlight' => false,
        ],
        [
            'number' => 3,
            'slug' => 'approve-design',
            'icon' => 'approval',
            'title' => 'Digital Artwork Approval',
            'description' => 'Receive a digital proof and approve before production begins.',
            'image' => $i['proof'],
            'alt' => 'Digital artwork proof approval before printing',
            'badge' => 'Approval Required',
            'trust' => null,
            'highlight' => true,
        ],
        [
            'number' => 4,
            'slug' => 'production',
            'icon' => 'print',
            'title' => 'Production & Quality Control',
            'description' => 'Your project enters production and passes through multiple quality checks.',
            'image' => $i['print_press'],
            'alt' => 'Printing equipment and production quality control',
            'badge' => null,
            'trust' => null,
            'highlight' => false,
        ],
        [
            'number' => 5,
            'slug' => 'finishing',
            'icon' => 'package',
            'title' => 'Finishing & Packaging',
            'description' => 'Cutting, laminating, binding, packaging and final inspection.',
            'image' => $i['finishing'],
            'alt' => 'Print finishing, binding and premium packaging',
            'badge' => null,
            'trust' => null,
            'highlight' => false,
        ],
        [
            'number' => 6,
            'slug' => 'delivery',
            'icon' => 'delivery',
            'title' => 'Delivery & Collection',
            'description' => 'Collect from our offices or receive delivery to your location.',
            'image' => $i['delivery'],
            'alt' => 'Order delivery and package collection handover',
            'badge' => null,
            'trust' => null,
            'highlight' => false,
        ],
    ],

    'trust_panel' => [
        'title' => 'Why Customers Love This Process',
        'headline' => 'No production starts without your approval.',
        'points' => [
            'Clear pricing before production',
            'Artwork approval before printing',
            'Quality checks at every stage',
            'Production visibility from press to dispatch',
            'Professional communication at every stage',
            'Reliable delivery across Kenya',
        ],
    ],

    'assurance' => [
        'title' => 'No printing starts without customer approval.',
        'subtitle' => 'Every job is proofed and signed off before we press print.',
    ],

];
