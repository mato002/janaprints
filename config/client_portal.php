<?php

return [

    'nav' => [
        ['label' => 'Overview', 'route' => 'client.dashboard', 'icon' => 'home'],
        ['label' => 'Quotes', 'route' => 'client.quotations.index', 'icon' => 'document'],
        ['label' => 'Orders', 'route' => 'client.orders.index', 'icon' => 'clipboard'],
        ['label' => 'Production jobs', 'route' => 'client.jobs.index', 'icon' => 'clipboard', 'active_routes' => ['client.jobs.*']],
        ['label' => 'Invoices', 'route' => 'client.invoices.index', 'icon' => 'receipt'],
        ['label' => 'Statements', 'route' => 'client.statements.index', 'icon' => 'document'],
        ['label' => 'Payments', 'route' => 'client.payments.index', 'icon' => 'banknotes'],
        ['label' => 'Artwork', 'route' => 'client.artwork.index', 'icon' => 'palette', 'active_routes' => ['client.artwork.*', 'client.artwork-library.*']],
        ['label' => 'Repeat order', 'route' => 'client.repeat-orders.index', 'icon' => 'clipboard'],
        ['label' => 'Communications', 'route' => 'client.communications.index', 'icon' => 'chat', 'active_routes' => ['client.communications.*']],
        ['label' => 'Account', 'route' => 'client.account.edit', 'icon' => 'user'],
    ],

];
