<?php

$i = require __DIR__ . '/public-images.php';

return [

    'stats' => [
        ['value' => 5000, 'suffix' => '+', 'label' => 'Projects Delivered'],
        ['value' => 1200, 'suffix' => '+', 'label' => 'Customers Served'],
        ['value' => 98, 'suffix' => '%', 'label' => 'On-Time Delivery'],
        ['value' => 50, 'suffix' => '+', 'label' => 'Corporate Clients'],
        ['value' => 100, 'suffix' => '%', 'label' => 'Artwork Approval Workflow'],
    ],

    'features' => [
        [
            'slug' => 'artwork-approval',
            'number' => '01',
            'title' => 'Approve Before We Print',
            'description' => 'Every project goes through a structured artwork approval process before production begins. This minimizes costly mistakes and ensures customers receive exactly what they approved.',
            'trust' => 'No printing without customer approval.',
            'image' => $i['proof'],
            'alt' => 'Digital artwork proof approval mockup on screen',
            'accent' => 'from-brand-magenta to-brand-purple',
            'featured' => true,
        ],
        [
            'slug' => 'quality-control',
            'number' => '02',
            'title' => 'Built-In Quality Assurance',
            'description' => 'Projects pass through multiple inspection points during production, finishing and packaging.',
            'trust' => 'Quality checked before delivery.',
            'image' => $i['finishing'],
            'alt' => 'Print quality inspection and finishing checkpoint',
            'accent' => 'from-brand-cyan to-brand-purple',
            'featured' => false,
        ],
        [
            'slug' => 'account-management',
            'number' => '03',
            'title' => 'A Dedicated Team Behind Every Project',
            'description' => 'Customers receive professional support from quotation through production and delivery.',
            'trust' => 'One point of contact throughout the project.',
            'image' => $i['team'],
            'alt' => 'Customer service team providing professional project support',
            'accent' => 'from-brand-orange to-brand-magenta',
            'featured' => false,
        ],
        [
            'slug' => 'fast-turnaround',
            'number' => '04',
            'title' => 'Fast & Reliable Delivery',
            'description' => 'Efficient production workflows help us meet tight deadlines without compromising quality.',
            'trust' => 'On-time project completion.',
            'image' => $i['office'],
            'alt' => 'Production scheduling and project timeline planning',
            'accent' => 'from-brand-purple to-brand-cyan',
            'featured' => false,
        ],
        [
            'slug' => 'nationwide-delivery',
            'number' => '05',
            'title' => 'We Deliver Across Kenya',
            'description' => 'Receive your printed materials wherever your business operates.',
            'trust' => 'Convenient delivery options.',
            'image' => $i['delivery'],
            'alt' => 'Nationwide delivery and logistics across Kenya',
            'accent' => 'from-brand-navy to-brand-cyan',
            'featured' => false,
        ],
        [
            'slug' => 'full-service',
            'number' => '06',
            'title' => 'More Than A Print Shop',
            'description' => 'Design, branding, packaging, large-format printing, promotional merchandise and production management under one roof.',
            'trust' => 'One partner for all branding needs.',
            'image' => $i['merchandise'],
            'alt' => 'Full-service branding and print production ecosystem',
            'accent' => 'from-brand-magenta to-brand-orange',
            'featured' => false,
        ],
    ],

    'comparison' => [
        'title' => 'The Jana Prints Difference',
        'traditional' => [
            'label' => 'Traditional Print Shop',
            'items' => [
                'Basic printing only',
                'Limited communication',
                'No approval workflow',
                'Limited tracking',
                'Manual process',
            ],
        ],
        'jana' => [
            'label' => 'Jana Prints',
            'items' => [
                'Structured artwork approval',
                'Full project visibility',
                'Quality checkpoints',
                'Dedicated support',
                'Production workflow',
                'Professional delivery',
            ],
        ],
    ],

    'confidence' => [
        'Professional Design Support',
        'Corporate Billing',
        'Project Management',
        'Quality Assurance',
        'Nationwide Delivery',
        'Dedicated Account Managers',
    ],

];
