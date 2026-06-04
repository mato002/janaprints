<?php

return [

    'name' => 'Jana Prints',
    'tagline' => 'Professional Printing & Branding',
    'url' => env('APP_URL', 'https://janaprints.com'),

    'seo' => [
        'title' => 'Jana Prints — Professional Printing, Branding & Packaging | Nairobi, Kenya',
        'description' => 'Kenya\'s trusted commercial printing partner. Business cards, packaging, large format, corporate branding and nationwide delivery. Request a quote today.',
        'keywords' => 'printing Nairobi, commercial printing Kenya, business cards, packaging, large format printing, branding, Jana Prints',
        'og_image' => '/images/og-jana-prints.jpg',
    ],

    'footer' => [
        'tagline' => 'Kenya\'s trusted partner for commercial printing, branding, packaging, and design. From business cards to large-format production — quality you can see, delivery you can count on.',
        'cta' => [
            'headline' => 'Ready to start your next print project?',
            'button' => 'Request A Free Quote',
            'href' => '#quote-form',
        ],
        'nav' => [
            ['label' => 'Services', 'href' => '#services'],
            ['label' => 'Our Work', 'href' => '#portfolio'],
            ['label' => 'Process', 'href' => '#workflow'],
            ['label' => 'Our Facility', 'href' => '#facility'],
            ['label' => 'Why Us', 'href' => '#why-us'],
            ['label' => 'Testimonials', 'href' => '#testimonials'],
            ['label' => 'Contact', 'href' => '#contact'],
        ],
        'trust_badges' => [
            '5000+ Projects Delivered',
            '98% On-Time Delivery',
            'Nationwide Delivery',
            'Artwork Approval Workflow',
        ],
        'social' => [
            ['label' => 'Facebook', 'href' => '#', 'icon' => 'facebook'],
            ['label' => 'Instagram', 'href' => '#', 'icon' => 'instagram'],
            ['label' => 'LinkedIn', 'href' => '#', 'icon' => 'linkedin'],
            ['label' => 'Twitter / X', 'href' => '#', 'icon' => 'twitter'],
        ],
    ],

];
