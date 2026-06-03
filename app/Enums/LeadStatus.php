<?php

namespace App\Enums;

enum LeadStatus: string
{
    case Open = 'open';
    case Won = 'won';
    case Lost = 'lost';
}
