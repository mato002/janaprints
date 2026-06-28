<?php

namespace App\Enums;

enum QualityReworkReason: string
{
    case WrongArtwork = 'wrong_artwork';
    case WrongQuantity = 'wrong_quantity';
    case BadPrint = 'bad_print';
    case WrongNumbering = 'wrong_numbering';
    case DamagedDuringFinishing = 'damaged_during_finishing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WrongArtwork => __('Wrong artwork'),
            self::WrongQuantity => __('Wrong quantity'),
            self::BadPrint => __('Bad print'),
            self::WrongNumbering => __('Wrong numbering'),
            self::DamagedDuringFinishing => __('Damaged during finishing'),
            self::Other => __('Other'),
        };
    }
}
