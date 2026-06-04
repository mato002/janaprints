<?php

namespace App\Enums;

enum PosPaymentMethod: string
{
    case Cash = 'cash';
    case Mpesa = 'mpesa';
    case Bank = 'bank';
    case Card = 'card';
}
