<?php

$i = require __DIR__ . '/public-images.php';

return [

    'blocks' => [
        [
            'slug' => 'production-floor',
            'number' => '01',
            'title' => 'Production Floor',
            'description' => 'Organised print production areas help every job move from approved artwork to output with control, visibility and consistency.',
            'bullets' => [
                'Press-ready job movement',
                'Production scheduling visibility',
                'Output checks before finishing',
            ],
            'image' => $i['production_floor'],
            'alt' => 'Jana Prints production floor with commercial printing equipment',
            'accent' => 'from-brand-magenta to-brand-purple',
        ],
        [
            'slug' => 'artwork-prepress',
            'number' => '02',
            'title' => 'Artwork & Pre-Press',
            'description' => 'Artwork is reviewed before printing so dimensions, bleed, colour expectations and approval status are clear.',
            'bullets' => [
                'File readiness checks',
                'Customer approval control',
                'Revision tracking',
            ],
            'image' => $i['prepress'],
            'alt' => 'Artwork review and pre-press preparation at Jana Prints',
            'accent' => 'from-brand-orange to-brand-magenta',
        ],
        [
            'slug' => 'finishing-packaging-dispatch',
            'number' => '03',
            'title' => 'Finishing, Packaging & Dispatch',
            'description' => 'Finished work is inspected, counted, protected and prepared for collection or delivery.',
            'bullets' => [
                'Final quality checks',
                'Packaging before release',
                'Dispatch visibility',
            ],
            'image' => $i['delivery'],
            'alt' => 'Packaging and dispatch area for finished print orders',
            'accent' => 'from-brand-navy to-brand-cyan',
        ],
    ],

];
