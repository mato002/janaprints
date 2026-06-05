<?php

namespace App\Enums;

enum AssetType: string
{
    case Machine = 'machine';
    case Vehicle = 'vehicle';
    case Computer = 'computer';
    case Printer = 'printer';
    case Plotter = 'plotter';
    case Furniture = 'furniture';
    case Generator = 'generator';
    case Network = 'network';
    case Office = 'office';
    case Tool = 'tool';
    case Leasehold = 'leasehold';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Machine => __('Machines'),
            self::Vehicle => __('Vehicles'),
            self::Computer => __('Computers'),
            self::Printer => __('Printers'),
            self::Plotter => __('Plotters'),
            self::Furniture => __('Furniture'),
            self::Generator => __('Generators'),
            self::Network => __('Networking Equipment'),
            self::Office => __('Office Equipment'),
            self::Tool => __('Tools'),
            self::Leasehold => __('Leasehold Improvements'),
            self::Other => __('Other'),
        };
    }
}
