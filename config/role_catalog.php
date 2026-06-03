<?php

/**
 * UI-only role classification for Access Control dashboards.
 * Does not alter authorization or role records.
 */

return [

    'categories' => [
        'administration' => ['label' => 'Administration', 'tone' => 'info'],
        'organization' => ['label' => 'Organization', 'tone' => 'neutral'],
        'crm' => ['label' => 'CRM', 'tone' => 'info'],
        'sales' => ['label' => 'Sales', 'tone' => 'success'],
        'artwork' => ['label' => 'Artwork', 'tone' => 'purple'],
        'production' => ['label' => 'Production', 'tone' => 'warning'],
        'inventory' => ['label' => 'Inventory', 'tone' => 'teal'],
        'finance' => ['label' => 'Finance', 'tone' => 'emerald'],
        'general' => ['label' => 'General', 'tone' => 'neutral'],
    ],

    'assignments' => [
        'Super Admin' => 'administration',
        'Company Admin' => 'administration',
        'Branch Manager' => 'organization',
        'Sales' => 'sales',
        'Designer' => 'artwork',
        'Production' => 'production',
        'Storekeeper' => 'inventory',
        'Accountant' => 'finance',
        'HR' => 'organization',
        'Viewer' => 'general',
    ],

];
