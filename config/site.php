<?php

return [

    'name' => 'Jana Prints',
    'tagline' => 'Professional Printing & Branding',
    'business_type' => 'Printing and branding company',
    'primary_service' => 'Printing, branding, design, and custom print production',
    'url' => env('APP_URL', 'https://janaprints.com'),

    'seo' => [
        'title' => 'Jana Prints — Professional Printing, Branding & Packaging | Nairobi, Kenya',
        'description' => 'Kenya\'s trusted commercial printing partner. Business cards, packaging, large format, corporate branding and nationwide delivery. Request a quote today.',
        'keywords' => 'printing services Kenya, printing services Nairobi, digital printing Kenya, offset printing Kenya, large format printing Kenya, branding services Kenya, custom printing Kenya, business cards printing Kenya, flyers printing Kenya, brochures printing Kenya, banners printing Kenya, corporate branding Kenya, bulk printing services Kenya, affordable printing services Kenya, Jana Prints',
        'og_image' => '/images/og-jana-prints.svg',
        'twitter_site' => null,
    ],

    'local' => [
        'company_name' => 'Jana Prints',
        'phone' => env('STOREFRONT_PHONE', '+254 700 000 000'),
        'email' => env('STOREFRONT_EMAIL', 'hello@janaprints.com'),
        'address' => env('STOREFRONT_ADDRESS', 'Industrial Area, Nairobi, Kenya'),
        'city' => env('STOREFRONT_CITY', 'Nairobi'),
        'country' => env('STOREFRONT_COUNTRY', 'Kenya'),
        'country_code' => 'KE',
        'opening_hours' => [
            'weekdays' => 'Mo-Fr 08:00-18:00',
            'saturday' => 'Sa 09:00-13:00',
        ],
        'google_maps_url' => env('STOREFRONT_MAPS_URL'),
        'whatsapp' => env('STOREFRONT_WHATSAPP', '254700000000'),
        'service_areas' => ['Kenya', 'Nairobi', 'Nationwide'],
        'logo' => '/images/jana-prints-logo.png',
        'favicon' => '/images/logo-mark.png',
        'sidebar_logo' => '/images/logo-sidebar.png',
    ],

    'branding_company_code' => env('SITE_BRANDING_COMPANY_CODE', 'JANA'),

    'analytics' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID'),
        'google_search_console_verification' => env('GOOGLE_SEARCH_CONSOLE_VERIFICATION'),
        'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
        'tiktok_pixel_id' => env('TIKTOK_PIXEL_ID'),
    ],

    'footer' => [
        'tagline' => 'Kenya\'s trusted partner for commercial printing, branding, packaging, and design. From business cards to large-format production — quality you can see, delivery you can count on.',
        'cta' => [
            'headline' => 'Ready to start your next print project?',
            'button' => 'Request A Free Quote',
            'href' => '/request-quote#quote-form',
        ],
        'nav' => [
            ['label' => 'Services', 'href' => '/services'],
            ['label' => 'Products', 'href' => '/products'],
            ['label' => 'Gallery', 'href' => '/gallery'],
            ['label' => 'About', 'href' => '/about'],
            ['label' => 'Guides', 'href' => '/blog'],
            ['label' => 'Contact', 'href' => '/contact'],
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
