<?php

namespace App\Enums;

enum ArtworkFileType: string
{
    case Pdf = 'pdf';
    case Ai = 'ai';
    case Psd = 'psd';
    case Cdr = 'cdr';
    case Svg = 'svg';
    case Png = 'png';
    case Jpg = 'jpg';
}
