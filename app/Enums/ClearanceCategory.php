<?php

namespace App\Enums;

enum ClearanceCategory: string
{
    case Assets = 'assets';
    case Documents = 'documents';
    case Finance = 'finance';
    case ItAccess = 'it_access';
    case Uniforms = 'uniforms';

    public function label(): string
    {
        return match ($this) {
            self::Assets => __('Assets'),
            self::Documents => __('Documents'),
            self::Finance => __('Finance'),
            self::ItAccess => __('IT Access'),
            self::Uniforms => __('Uniforms'),
        };
    }
}
