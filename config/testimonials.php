<?php

$i = require __DIR__ . '/public-images.php';

return [

    'featured' => [
        [
            'name' => 'Sarah M.',
            'organization' => 'Corporate Client',
            'location' => 'Nairobi',
            'project_type' => 'Annual Report Printing',
            'quote' => 'Jana Prints handled our annual report printing professionally and delivered ahead of schedule. The quality exceeded our expectations.',
            'photo' => $i['portrait'],
            'alt' => 'Marketing manager corporate client portrait placeholder',
        ],
        [
            'name' => 'James K.',
            'organization' => 'Private School',
            'location' => 'Eldoret',
            'project_type' => 'School Prospectus Production',
            'quote' => 'From design review to final delivery, the team kept us informed at every stage. Our prospectus print run was flawless.',
            'photo' => $i['portrait'],
            'alt' => 'School administrator client portrait placeholder',
        ],
        [
            'name' => 'Grace W.',
            'organization' => 'NGO Partner',
            'location' => 'Nakuru',
            'project_type' => 'Campaign Materials',
            'quote' => 'The approval workflow saved us from costly printing mistakes. We felt in control throughout the entire production process.',
            'photo' => $i['portrait'],
            'alt' => 'NGO procurement officer client portrait placeholder',
        ],
    ],

    'videos' => [
        [
            'role' => 'School Principal',
            'quote' => 'Reliable quality for every enrolment season.',
            'thumbnail' => $i['school'],
            'alt' => 'School principal video testimonial placeholder',
        ],
        [
            'role' => 'NGO Procurement Officer',
            'quote' => 'Transparent process from quote to delivery.',
            'thumbnail' => $i['team'],
            'alt' => 'NGO procurement officer video testimonial placeholder',
        ],
        [
            'role' => 'Corporate Marketing Manager',
            'quote' => 'Our rebrand rollout was seamless.',
            'thumbnail' => $i['office'],
            'alt' => 'Corporate marketing manager video testimonial placeholder',
        ],
        [
            'role' => 'Retail Business Owner',
            'quote' => 'Packaging that elevated our product launch.',
            'thumbnail' => $i['corporate'],
            'alt' => 'Retail business owner video testimonial placeholder',
        ],
    ],

    'success_stories' => [
        [
            'title' => 'Corporate Rebranding Project',
            'client_type' => 'Corporate',
            'challenge' => 'Rebrand multiple branches within tight timelines.',
            'solution' => 'Design + print + branding rollout across all locations.',
            'outcome' => 'Completed successfully in 5 days.',
        ],
        [
            'title' => 'School Prospectus Production',
            'client_type' => 'Education',
            'challenge' => 'Large-volume printing under a strict deadline.',
            'solution' => 'Managed production scheduling with digital proof approval.',
            'outcome' => '15,000 copies delivered on time.',
        ],
        [
            'title' => 'NGO Awareness Campaign',
            'client_type' => 'NGO',
            'challenge' => 'Nationwide campaign materials across multiple formats.',
            'solution' => 'Multi-format print production with coordinated delivery.',
            'outcome' => 'Successful project rollout across 6 regions.',
        ],
    ],

    'impact_stats' => [
        ['value' => 5000, 'suffix' => '+', 'label' => 'Projects Delivered'],
        ['value' => 1200, 'suffix' => '+', 'label' => 'Satisfied Customers'],
        ['value' => 250, 'suffix' => '+', 'label' => 'Schools Served'],
        ['value' => 50, 'suffix' => '+', 'label' => 'Corporate Accounts'],
        ['value' => 98, 'suffix' => '%', 'label' => 'On-Time Delivery'],
    ],

    'trust_categories' => [
        ['label' => 'Schools', 'icon' => 'school'],
        ['label' => 'Universities', 'icon' => 'university'],
        ['label' => 'NGOs', 'icon' => 'heart'],
        ['label' => 'Government', 'icon' => 'landmark'],
        ['label' => 'Hotels', 'icon' => 'hotel'],
        ['label' => 'Corporate Offices', 'icon' => 'building'],
        ['label' => 'Manufacturing', 'icon' => 'factory'],
        ['label' => 'Hospitals', 'icon' => 'hospital'],
        ['label' => 'Retail Businesses', 'icon' => 'retail'],
    ],

    'reviews' => [
        'Excellent quality.',
        'Fast delivery.',
        'Great communication.',
        'Professional support.',
        'Reliable every time.',
        'Outstanding print finish.',
        'Easy approval process.',
        'Highly recommended.',
    ],

];
