<?php

namespace App\Enums;

enum WebsiteGalleryCategory: string
{
    case BusinessCards = 'business-cards';
    case Brochures = 'brochures';
    case Flyers = 'flyers';
    case CorporateStationery = 'corporate-stationery';
    case Packaging = 'packaging';
    case LargeFormat = 'large-format';
    case VehicleBranding = 'vehicle-branding';
    case PromotionalMaterials = 'promotional-materials';
    case EventsExhibitions = 'events-exhibitions';
    case LabelsStickers = 'labels-stickers';
    case BrandingInstallations = 'branding-installations';

    public function label(): string
    {
        return match ($this) {
            self::BusinessCards => 'Business Cards',
            self::Brochures => 'Brochures',
            self::Flyers => 'Flyers',
            self::CorporateStationery => 'Corporate Stationery',
            self::Packaging => 'Packaging',
            self::LargeFormat => 'Large Format',
            self::VehicleBranding => 'Vehicle Branding',
            self::PromotionalMaterials => 'Promotional Materials',
            self::EventsExhibitions => 'Events & Exhibitions',
            self::LabelsStickers => 'Labels & Stickers',
            self::BrandingInstallations => 'Branding Installations',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
