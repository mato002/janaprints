<?php

$i = require __DIR__ . '/public-images.php';

return [

    'pipeline' => [
        [
            'title' => 'Artwork Review',
            'description' => 'Files checked for resolution, colour and print readiness.',
            'icon' => 'design',
            'image' => $i['artwork'],
            'alt' => 'Artwork review and pre-flight checking',
        ],
        [
            'title' => 'Pre-Press',
            'description' => 'Imposition, colour profiles and plate preparation.',
            'icon' => 'prepress',
            'image' => $i['prepress'],
            'alt' => 'Pre-press preparation and file setup',
        ],
        [
            'title' => 'Printing',
            'description' => 'Commercial presses run with calibrated colour output.',
            'icon' => 'print',
            'image' => $i['print_press'],
            'alt' => 'Commercial printing in progress',
        ],
        [
            'title' => 'Finishing',
            'description' => 'Cutting, folding, laminating and binding completed.',
            'icon' => 'finish',
            'image' => $i['finishing'],
            'alt' => 'Print finishing and trimming operations',
        ],
        [
            'title' => 'Quality Control',
            'description' => 'Multi-point inspection before items leave production.',
            'icon' => 'qc',
            'image' => $i['quality'],
            'alt' => 'Quality control inspection checkpoint',
        ],
        [
            'title' => 'Packaging',
            'description' => 'Jobs counted, protected and prepared for dispatch.',
            'icon' => 'package',
            'image' => $i['packaging'],
            'alt' => 'Order packaging and preparation',
        ],
        [
            'title' => 'Delivery',
            'description' => 'Collection or nationwide delivery to your location.',
            'icon' => 'delivery',
            'image' => $i['delivery'],
            'alt' => 'Dispatch and delivery handover',
        ],
    ],

    'facility_areas' => [
        [
            'title' => 'Production Floor',
            'description' => 'Organised press lines built for high-volume commercial output with consistent colour across every run.',
            'image' => $i['banner'],
            'alt' => 'Jana Prints production floor with commercial printing equipment',
        ],
        [
            'title' => 'Printing Equipment',
            'description' => 'Modern digital and offset capabilities calibrated for precision colour reproduction on every job.',
            'image' => $i['print_press'],
            'alt' => 'Professional printing presses and equipment',
        ],
        [
            'title' => 'Finishing Area',
            'description' => 'Dedicated finishing stations for laminating, binding, trimming and specialty post-press work.',
            'image' => $i['finishing'],
            'alt' => 'Print finishing area with cutting and binding equipment',
        ],
        [
            'title' => 'Packaging Area',
            'description' => 'Finished jobs are counted, protected and labelled before they move to dispatch.',
            'image' => $i['packaging'],
            'alt' => 'Packaging area for finished print orders',
        ],
        [
            'title' => 'Dispatch Section',
            'description' => 'Orders staged for collection or routed for nationwide delivery with full tracking visibility.',
            'image' => $i['delivery'],
            'alt' => 'Dispatch section ready for order collection and delivery',
        ],
    ],

    'team' => [
        [
            'name' => 'Management Team',
            'role' => 'Leadership & Operations',
            'bio' => 'Oversees production planning, client commitments and operational excellence across every department.',
            'photo' => $i['team'],
            'alt' => 'Jana Prints management team placeholder',
        ],
        [
            'name' => 'Design Team',
            'role' => 'Artwork & Pre-Press',
            'bio' => 'Reviews artwork, prepares print-ready files and supports clients through the approval process.',
            'photo' => $i['artwork'],
            'alt' => 'Jana Prints design team placeholder',
        ],
        [
            'name' => 'Production Team',
            'role' => 'Press & Output',
            'bio' => 'Runs jobs through calibrated presses with attention to colour accuracy and production schedules.',
            'photo' => $i['portrait'],
            'alt' => 'Jana Prints production team placeholder',
        ],
        [
            'name' => 'Quality Team',
            'role' => 'Inspection & Standards',
            'bio' => 'Conducts multi-stage quality checks to ensure every order meets our standards before dispatch.',
            'photo' => $i['portrait'],
            'alt' => 'Jana Prints quality team placeholder',
        ],
        [
            'name' => 'Customer Support',
            'role' => 'Client Success',
            'bio' => 'Your dedicated point of contact from quotation through production updates and delivery.',
            'photo' => $i['office'],
            'alt' => 'Jana Prints customer support team placeholder',
        ],
        [
            'name' => 'Delivery Team',
            'role' => 'Logistics & Dispatch',
            'bio' => 'Coordinates collection and nationwide delivery so your finished products arrive on schedule.',
            'photo' => $i['delivery'],
            'alt' => 'Jana Prints delivery team placeholder',
        ],
    ],

    'quality_steps' => [
        ['number' => 1, 'title' => 'Artwork Verification', 'description' => 'Files validated for resolution, bleed and colour accuracy before production.'],
        ['number' => 2, 'title' => 'Print Quality Inspection', 'description' => 'Press output checked against approved proofs during the production run.'],
        ['number' => 3, 'title' => 'Color Accuracy Check', 'description' => 'Colour consistency verified against client-approved standards.'],
        ['number' => 4, 'title' => 'Finishing Inspection', 'description' => 'Cutting, binding and lamination reviewed for precision and finish quality.'],
        ['number' => 5, 'title' => 'Packaging Verification', 'description' => 'Quantities counted and packaging integrity confirmed before dispatch.'],
        ['number' => 6, 'title' => 'Dispatch Confirmation', 'description' => 'Final sign-off and tracking details shared with the client.'],
    ],

    'capabilities' => [
        [
            'title' => 'Digital Printing',
            'description' => 'Fast-turnaround digital output for short runs, proofs and variable data jobs.',
            'output' => 'Business cards, flyers, labels',
            'image' => $i['prepress'],
            'alt' => 'Digital printing capability',
        ],
        [
            'title' => 'Offset Printing',
            'description' => 'High-volume offset production for consistent colour across large print runs.',
            'output' => 'Brochures, catalogues, reports',
            'image' => $i['print_press'],
            'alt' => 'Offset printing capability',
        ],
        [
            'title' => 'Large Format Printing',
            'description' => 'Banners, billboards, backdrops and exhibition displays at scale.',
            'output' => 'Banners, signage, backdrops',
            'image' => $i['banner'],
            'alt' => 'Large format printing capability',
        ],
        [
            'title' => 'Lamination',
            'description' => 'Matt, gloss and soft-touch lamination for durability and premium feel.',
            'output' => 'Cards, menus, covers',
            'image' => $i['finishing'],
            'alt' => 'Lamination finishing capability',
        ],
        [
            'title' => 'Binding',
            'description' => 'Saddle-stitch, perfect bind and wire-o binding for professional documents.',
            'output' => 'Prospectuses, reports, booklets',
            'image' => $i['brochure'],
            'alt' => 'Document binding capability',
        ],
        [
            'title' => 'Packaging Production',
            'description' => 'Custom boxes, labels and retail packaging for product launches.',
            'output' => 'Boxes, labels, retail packs',
            'image' => $i['packaging'],
            'alt' => 'Packaging production capability',
        ],
        [
            'title' => 'Branding & Signage',
            'description' => 'Indoor and outdoor signage, wall graphics and directional branding.',
            'output' => 'Signage, wall graphics, displays',
            'image' => $i['print_press'],
            'alt' => 'Branding and signage capability',
        ],
        [
            'title' => 'Vehicle Branding',
            'description' => 'Durable fleet wraps and vehicle graphics for mobile brand presence.',
            'output' => 'Fleet wraps, decals, bonnet logos',
            'image' => $i['vehicle'],
            'alt' => 'Vehicle branding capability',
        ],
    ],

    'trust_points' => [
        'Quality Assurance',
        'Consistent Output',
        'Reduced Errors',
        'Faster Turnaround',
        'Better Communication',
        'Reliable Delivery',
    ],

    'gallery' => [
        ['image' => $i['print_press'], 'alt' => 'Printing in progress on commercial press', 'layout' => 'tall'],
        ['image' => $i['packaging'], 'alt' => 'Packaging finished print orders', 'layout' => 'normal'],
        ['image' => $i['finishing'], 'alt' => 'Finished print products quality check', 'layout' => 'wide'],
        ['image' => $i['delivery'], 'alt' => 'Dispatch and delivery preparation', 'layout' => 'normal'],
        ['image' => $i['banner'], 'alt' => 'Large format production run', 'layout' => 'hero'],
        ['image' => $i['vehicle'], 'alt' => 'Vehicle branding installation', 'layout' => 'normal'],
        ['image' => $i['artwork'], 'alt' => 'Design team collaboration', 'layout' => 'tall'],
        ['image' => $i['brochure'], 'alt' => 'Finished brochure and marketing collateral', 'layout' => 'normal'],
    ],

];
