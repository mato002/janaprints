<?php

return [

    'nav' => [
        ['label' => 'Overview', 'route' => 'client.dashboard', 'icon' => 'home'],
        ['label' => 'Quotes', 'route' => 'client.quotations.index', 'icon' => 'document'],
        ['label' => 'Orders', 'route' => 'client.orders.index', 'icon' => 'clipboard'],
        ['label' => 'Invoices', 'route' => 'client.invoices.index', 'icon' => 'receipt'],
        ['label' => 'Payments', 'route' => 'client.payments.index', 'icon' => 'banknotes'],
        ['label' => 'Artwork', 'route' => 'client.artwork.index', 'icon' => 'palette'],
        ['label' => 'Account', 'route' => 'client.account.edit', 'icon' => 'user'],
    ],

];
