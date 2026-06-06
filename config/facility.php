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

    'production_showcase' => [
        'hero_image' => $i['hero'],
        'hero_alt' => 'Jana Prints production floor with commercial printing equipment',
        'flow' => ['Artwork', 'Printing', 'Finishing', 'Quality Check', 'Packaging', 'Delivery'],
        'steps' => [
            [
                'title' => 'Production Floor',
                'description' => 'Organised press lines built for high-volume commercial output with consistent colour across every run.',
                'icon' => 'floor',
                'image' => $i['production_floor'],
                'alt' => 'Production floor with commercial printing equipment',
            ],
            [
                'title' => 'Pre-Press & Artwork Review',
                'description' => 'Designers and pre-press specialists verify files, colour profiles and print readiness before any job enters production.',
                'icon' => 'prepress',
                'image' => $i['prepress'],
                'alt' => 'Pre-press and artwork review team at work',
            ],
            [
                'title' => 'Printing',
                'description' => 'Digital and offset presses calibrated for precision colour reproduction on every commercial print run.',
                'icon' => 'print',
                'image' => $i['print_press'],
                'alt' => 'Professional printing presses in operation',
            ],
            [
                'title' => 'Finishing',
                'description' => 'Cutting, laminating, binding and specialty post-press work completed in dedicated finishing stations.',
                'icon' => 'finish',
                'image' => $i['finishing'],
                'alt' => 'Print finishing and cutting operations',
            ],
            [
                'title' => 'Packaging',
                'description' => 'Finished jobs are counted, protected and labelled before they move to dispatch.',
                'icon' => 'package',
                'image' => $i['packaging'],
                'alt' => 'Packaging area for finished print orders',
            ],
            [
                'title' => 'Dispatch',
                'description' => 'Orders staged for collection or routed for nationwide delivery with full tracking visibility.',
                'icon' => 'delivery',
                'image' => $i['delivery'],
                'alt' => 'Dispatch section ready for order collection and delivery',
            ],
        ],
    ],

    'team' => [
        [
            'name' => 'Management Team',
            'role' => 'Leadership & Operations',
            'bio' => 'Coordinates production planning, customer commitments and operational discipline across every department.',
            'photo' => '/images/storefront/team/management.jpg',
            'alt' => 'Jana Prints management team coordinating production planning',
        ],
        [
            'name' => 'Design Team',
            'role' => 'Artwork & Pre-Press',
            'bio' => 'Reviews artwork, prepares print-ready files and supports customers through the approval process.',
            'photo' => '/images/storefront/team/design.jpg',
            'alt' => 'Design team reviewing print artwork at a pre-press workstation',
        ],
        [
            'name' => 'Production Team',
            'role' => 'Press & Output',
            'bio' => 'Runs approved jobs through calibrated production workflows with attention to colour, size and finishing accuracy.',
            'photo' => '/images/storefront/team/production.jpg',
            'alt' => 'Production team operating commercial printing equipment',
        ],
        [
            'name' => 'Quality Team',
            'role' => 'Inspection & Standards',
            'bio' => 'Checks work at key stages so orders meet customer expectations before dispatch.',
            'photo' => '/images/storefront/quality/color.jpg',
            'alt' => 'Quality team inspecting colour accuracy on finished print work',
        ],
        [
            'name' => 'Customer Support',
            'role' => 'Client Success',
            'bio' => 'Keeps customers informed from quotation through production updates and delivery.',
            'photo' => '/images/storefront/team/support.jpg',
            'alt' => 'Customer support team assisting print clients',
        ],
        [
            'name' => 'Delivery Team',
            'role' => 'Logistics & Dispatch',
            'bio' => 'Coordinates collection and delivery so finished products reach customers on schedule.',
            'photo' => '/images/storefront/team/dispatch.jpg',
            'alt' => 'Delivery team preparing finished print orders for dispatch',
        ],
    ],

    'quality_promise' => [
        'banner' => 'No production starts without customer approval.',
        'steps' => [
            [
                'number' => '01',
                'title' => 'Artwork Approval',
                'value' => 'Every file is reviewed and signed off by the client before plates or presses are engaged.',
                'image' => '/images/storefront/quality/artwork.jpg',
                'alt' => 'Artwork approval and design review process',
            ],
            [
                'number' => '02',
                'title' => 'Pre-Press Verification',
                'value' => 'Colour profiles, bleed and resolution are verified to eliminate costly production errors.',
                'image' => '/images/storefront/quality/prepress.jpg',
                'alt' => 'Pre-press verification on production equipment',
            ],
            [
                'number' => '03',
                'title' => 'Color Consistency Check',
                'value' => 'Press output is matched against approved proofs for consistent brand colour across every run.',
                'image' => '/images/storefront/quality/color.jpg',
                'alt' => 'Colour consistency check during printing',
            ],
            [
                'number' => '04',
                'title' => 'Finishing Inspection',
                'value' => 'Cutting, binding and lamination are inspected for precision before items move to packaging.',
                'image' => '/images/storefront/quality/finishing.jpg',
                'alt' => 'Finishing inspection of printed materials',
            ],
            [
                'number' => '05',
                'title' => 'Packaging Review',
                'value' => 'Quantities are counted and packaging integrity confirmed to protect finished work in transit.',
                'image' => '/images/storefront/quality/packaging.jpg',
                'alt' => 'Packaging review before dispatch',
            ],
            [
                'number' => '06',
                'title' => 'Delivery Confirmation',
                'value' => 'Final sign-off and tracking details are shared so clients know exactly when to expect delivery.',
                'image' => '/images/storefront/quality/delivery.jpg',
                'alt' => 'Delivery confirmation and dispatch handover',
            ],
        ],
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
            'image' => $i['signage'],
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
        ['image' => '/images/storefront/gallery/print-production.jpg', 'alt' => 'Printing in progress on commercial press', 'layout' => 'tall'],
        ['image' => '/images/storefront/gallery/packaging.jpg', 'alt' => 'Packaging finished print orders', 'layout' => 'normal'],
        ['image' => '/images/storefront/gallery/large-format.jpg', 'alt' => 'Large format banner production', 'layout' => 'wide'],
        ['image' => '/images/storefront/gallery/business-cards.jpg', 'alt' => 'Premium business card printing', 'layout' => 'normal'],
        ['image' => '/images/storefront/gallery/vehicle-branding.jpg', 'alt' => 'Vehicle branding installation', 'layout' => 'hero'],
        ['image' => '/images/storefront/gallery/stationery.jpg', 'alt' => 'Corporate stationery suite printing', 'layout' => 'normal'],
        ['image' => '/images/storefront/gallery/design-studio.jpg', 'alt' => 'Design team collaboration on print project', 'layout' => 'tall'],
        ['image' => '/images/storefront/gallery/brochures.jpg', 'alt' => 'Finished brochure and marketing collateral', 'layout' => 'normal'],
    ],

];
