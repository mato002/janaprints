<?php

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Meeting = 'meeting';
    case Email = 'email';
    case Whatsapp = 'whatsapp';
    case Sms = 'sms';
    case Visit = 'visit';
}
