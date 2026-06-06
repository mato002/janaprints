<?php

$i = require __DIR__ . '/public-images.php';

// Placeholder coordinates — update when the production facility pin is confirmed.
$mapLatitude = -1.303206;
$mapLongitude = 36.817223;
$mapZoom = 15;

return [

    'contact' => [
        'phone' => '+254 700 000 000',
        'phone_href' => 'tel:+254700000000',
        'email' => 'hello@janaprints.com',
        'email_href' => 'mailto:hello@janaprints.com',
        'address' => 'Industrial Area, Nairobi, Kenya',
        'address_detail' => 'Visit our production facility by appointment.',
        'hours' => 'Mon – Fri: 8:00 AM – 6:00 PM',
        'hours_weekend' => 'Sat: 9:00 AM – 1:00 PM',
        'map_latitude' => $mapLatitude,
        'map_longitude' => $mapLongitude,
        'map_zoom' => $mapZoom,
        'map_embed' => sprintf(
            'https://maps.google.com/maps?q=%F,%F&z=%d&hl=en&ie=UTF8&iwloc=&output=embed',
            $mapLatitude,
            $mapLongitude,
            $mapZoom,
        ),
        'map_url' => 'https://www.google.com/maps/search/?api=1&query=Industrial+Area+Nairobi+Kenya',
        'map_placeholder' => 'Industrial Area, Nairobi, Kenya',
    ],

    'inquiry_types' => [
        ['value' => 'Request a Quote', 'slug' => 'quote'],
        ['value' => 'Send Artwork', 'slug' => 'artwork'],
        ['value' => 'General Inquiry', 'slug' => 'general'],
        ['value' => 'Book Consultation', 'slug' => 'consultation'],
        ['value' => 'Follow Up Existing Order', 'slug' => 'follow-up'],
    ],

    'final_cta' => [
        'title' => 'Ready To Start Your Next Print Project?',
        'subtitle' => 'Get pricing, artwork guidance, and production support from our team.',
    ],

    'whatsapp' => [
        'number' => '254700000000',
        'message' => 'Hello Jana Prints, I would like to enquire about a print project.',
        'response_time' => 'Typically within 15 minutes',
        'availability' => 'Mon – Sat during business hours',
        'hours' => '8:00 AM – 6:00 PM (Mon – Fri)',
    ],

    'primary_cta' => [
        'headline' => "Let's Start Your Next Project",
        'description' => 'Receive a quotation within minutes and professional guidance from our team.',
        'image' => $i['print_press'],
        'alt' => 'Premium commercial printing production',
    ],

    'action_cards' => [
        [
            'slug' => 'quote',
            'title' => 'Request A Quote',
            'description' => 'Upload requirements and receive pricing.',
            'icon' => 'document',
            'href' => '#quote-form',
            'accent' => 'magenta',
        ],
        [
            'slug' => 'artwork',
            'title' => 'Send Artwork',
            'description' => 'Already have artwork? Upload and get started.',
            'icon' => 'upload',
            'href' => '#quote-form',
            'accent' => 'purple',
        ],
        [
            'slug' => 'whatsapp',
            'title' => 'WhatsApp Us',
            'description' => 'Talk to a real person instantly.',
            'icon' => 'whatsapp',
            'href' => 'whatsapp',
            'accent' => 'green',
        ],
        [
            'slug' => 'consultation',
            'title' => 'Book Consultation',
            'description' => 'Discuss your project with our team.',
            'icon' => 'calendar',
            'href' => '#contact',
            'accent' => 'cyan',
        ],
    ],

    'services' => [
        'Business Cards',
        'Corporate Stationery',
        'Brochures & Flyers',
        'Packaging',
        'Large Format / Banners',
        'Branding & Signage',
        'Vehicle Branding',
        'Other',
    ],

    'faq' => [
        [
            'question' => 'How long does printing take?',
            'answer' => 'Turnaround depends on the job type and quantity. Standard business cards and stationery typically take 2–5 business days. Large format, packaging and bulk orders may require 5–10 business days. Rush options are available — share your deadline when requesting a quote.',
        ],
        [
            'question' => 'Can I approve artwork before printing?',
            'answer' => 'Yes. Every project includes a digital proof for your review and approval before production begins. We do not proceed to print until you sign off on the artwork.',
        ],
        [
            'question' => 'Do you offer delivery?',
            'answer' => 'We offer collection from our Nairobi facility and nationwide delivery across Kenya. Delivery timelines and costs are confirmed with your quotation.',
        ],
        [
            'question' => 'Can you help with design?',
            'answer' => 'Our design team can create artwork from scratch, refine existing files, or prepare print-ready layouts. Mention your design needs when submitting a quote request.',
        ],
        [
            'question' => 'What file formats do you accept?',
            'answer' => 'We accept PDF, AI, EPS, PSD, and high-resolution JPG/PNG files. For best results, supply print-ready PDFs with embedded fonts and 3 mm bleed where applicable.',
        ],
        [
            'question' => 'Can you handle bulk orders?',
            'answer' => 'Absolutely. We run commercial-scale production for corporate accounts, campaigns, and high-volume print runs. Contact us for dedicated account pricing.',
        ],
        [
            'question' => 'How do I request a quotation?',
            'answer' => 'Fill in the quote form below, WhatsApp us, or call our team directly. Include your service type, quantity, deadline, and artwork if available — we respond with pricing promptly.',
        ],
    ],

    'trust_strip' => [
        ['type' => 'counter', 'value' => 5000, 'suffix' => '+', 'label' => 'Projects Delivered'],
        ['type' => 'counter', 'value' => 1200, 'suffix' => '+', 'label' => 'Customers'],
        ['type' => 'counter', 'value' => 98, 'suffix' => '%', 'label' => 'On-Time Delivery'],
        ['type' => 'text', 'label' => 'Nationwide Delivery'],
        ['type' => 'text', 'label' => 'Artwork Approval Workflow'],
        ['type' => 'text', 'label' => 'Dedicated Support'],
    ],

    'branches' => [
        // Future-ready branch locations
        // ['name' => 'Nairobi HQ', 'address' => 'Industrial Area, Nairobi', 'phone' => '+254 700 000 000'],
    ],

];
