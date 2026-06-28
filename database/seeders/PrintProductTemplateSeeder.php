<?php

namespace Database\Seeders;

use App\Enums\PrintProductTemplateCategory;
use App\Enums\ProductionType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\PrintProductTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrintProductTemplateSeeder extends Seeder
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function presetDefinitions(): array
    {
        return [
            ['code' => 'BUSINESS-CARD', 'name' => 'Business Cards', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'gsm' => '350', 'default_finished_size' => '90x50mm', 'default_colour_mode' => '4/4', 'number_of_colours' => 4, 'default_sides' => 'double', 'default_lamination' => true, 'default_finishing_type' => 'matt_lamination', 'default_waste_allowance_percent' => 5, 'default_ups' => 10],
            ['code' => 'LETTERHEAD', 'name' => 'Letterheads', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'gsm' => '100', 'default_finished_size' => 'A4', 'default_colour_mode' => '4/0', 'default_sides' => 'single'],
            ['code' => 'ENVELOPE', 'name' => 'Envelopes', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'default_finished_size' => 'DL', 'default_colour_mode' => '4/0'],
            ['code' => 'FLYER', 'name' => 'Flyers', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'gsm' => '150', 'default_finished_size' => 'A5', 'default_colour_mode' => '4/4', 'default_sides' => 'double'],
            ['code' => 'BROCHURE', 'name' => 'Brochures', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'gsm' => '170', 'default_finished_size' => 'A4', 'default_binding_type' => 'saddle_stitch'],
            ['code' => 'POSTER', 'name' => 'Posters', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::LargeFormat, 'default_finished_size' => 'A2'],
            ['code' => 'STICKER', 'name' => 'Stickers', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'default_finishing_type' => 'kiss_cut', 'default_die_cutting' => true],
            ['code' => 'LABEL', 'name' => 'Labels', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'default_die_cutting' => true],
            ['code' => 'RECEIPT-BOOK', 'name' => 'Receipt Books', 'category' => PrintProductTemplateCategory::SecurityPrinting, 'production_type' => ProductionType::Offset, 'default_finishing_type' => 'ncr', 'default_numbering_required' => true, 'default_perforation' => true, 'default_binding_type' => 'top', 'default_notes' => 'NCR duplicate/triplicate as required'],
            ['code' => 'INVOICE-BOOK', 'name' => 'Invoice Books', 'category' => PrintProductTemplateCategory::SecurityPrinting, 'production_type' => ProductionType::Offset, 'default_finishing_type' => 'ncr', 'default_numbering_required' => true, 'default_perforation' => true, 'default_binding_type' => 'top'],
            ['code' => 'DELIVERY-NOTE', 'name' => 'Delivery Notes', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'default_finished_size' => 'A5'],
            ['code' => 'DELIVERY-BOOK', 'name' => 'Delivery Books', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'default_finishing_type' => 'ncr', 'default_numbering_required' => true, 'default_perforation' => true, 'default_binding_type' => 'top'],
            ['code' => 'NCR-BOOK', 'name' => 'NCR Books', 'category' => PrintProductTemplateCategory::SecurityPrinting, 'production_type' => ProductionType::Offset, 'default_finishing_type' => 'ncr', 'default_numbering_required' => true, 'default_perforation' => true, 'default_binding_type' => 'top'],
            ['code' => 'CERTIFICATE', 'name' => 'Certificates', 'category' => PrintProductTemplateCategory::CorporateBranding, 'production_type' => ProductionType::Digital, 'gsm' => '250', 'default_finished_size' => 'A4'],
            ['code' => 'CALENDAR', 'name' => 'Calendars', 'category' => PrintProductTemplateCategory::Promotional, 'production_type' => ProductionType::Offset, 'default_binding_type' => 'wire_o'],
            ['code' => 'DIARY', 'name' => 'Diaries', 'category' => PrintProductTemplateCategory::Promotional, 'production_type' => ProductionType::Offset, 'default_binding_type' => 'perfect_bound'],
            ['code' => 'FILE-FOLDER', 'name' => 'Files & Folders', 'category' => PrintProductTemplateCategory::Stationery, 'production_type' => ProductionType::Offset, 'default_creasing' => true],
            ['code' => 'PVC-CARD', 'name' => 'PVC Cards', 'category' => PrintProductTemplateCategory::Promotional, 'production_type' => ProductionType::Digital, 'default_finished_size' => 'CR80', 'default_lamination' => true],
            ['code' => 'ROLLUP-BANNER', 'name' => 'Roll-up Banners', 'category' => PrintProductTemplateCategory::LargeFormat, 'production_type' => ProductionType::LargeFormat, 'default_material_inventory_item_id' => null, 'default_notes' => 'PVC + aluminium stand', 'default_eyelets' => false],
            ['code' => 'BANNER', 'name' => 'Banners', 'category' => PrintProductTemplateCategory::LargeFormat, 'production_type' => ProductionType::LargeFormat, 'default_finishing_type' => 'flex', 'default_eyelets' => true, 'default_notes' => 'Outdoor flex with eyelets'],
            ['code' => 'MESH-BANNER', 'name' => 'Mesh Banners', 'category' => PrintProductTemplateCategory::LargeFormat, 'production_type' => ProductionType::LargeFormat, 'default_finishing_type' => 'mesh', 'default_eyelets' => true],
            ['code' => 'VINYL', 'name' => 'Vinyl', 'category' => PrintProductTemplateCategory::LargeFormat, 'production_type' => ProductionType::LargeFormat, 'default_finishing_type' => 'vinyl'],
            ['code' => 'CANVAS', 'name' => 'Canvas', 'category' => PrintProductTemplateCategory::LargeFormat, 'production_type' => ProductionType::LargeFormat, 'default_finishing_type' => 'canvas'],
            ['code' => 'PACKAGING-BOX', 'name' => 'Packaging Boxes', 'category' => PrintProductTemplateCategory::Packaging, 'production_type' => ProductionType::Offset, 'default_die_cutting' => true, 'default_creasing' => true],
            ['code' => 'PAPER-BAG', 'name' => 'Paper Bags', 'category' => PrintProductTemplateCategory::Packaging, 'production_type' => ProductionType::Offset],
            ['code' => 'TENT-CARD', 'name' => 'Tent Cards', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'default_creasing' => true],
            ['code' => 'MENU', 'name' => 'Menus', 'category' => PrintProductTemplateCategory::Marketing, 'production_type' => ProductionType::Digital, 'default_lamination' => true],
            ['code' => 'MAGAZINE', 'name' => 'Magazines', 'category' => PrintProductTemplateCategory::Books, 'production_type' => ProductionType::Offset, 'default_binding_type' => 'perfect_bound'],
            ['code' => 'BOOK', 'name' => 'Books', 'category' => PrintProductTemplateCategory::Books, 'production_type' => ProductionType::Offset, 'default_binding_type' => 'perfect_bound'],
            ['code' => 'CORP-PROFILE', 'name' => 'Corporate Profiles', 'category' => PrintProductTemplateCategory::CorporateBranding, 'production_type' => ProductionType::Offset, 'default_binding_type' => 'perfect_bound'],
            ['code' => 'CUSTOM', 'name' => 'Custom Product', 'category' => PrintProductTemplateCategory::Custom, 'production_type' => ProductionType::Mixed, 'description' => 'Blank preset for non-standard jobs'],
        ];
    }

    public function run(): void
    {
        $company = Company::query()->first();
        $branch = $company ? Branch::query()->where('company_id', $company->id)->first() : null;
        $user = User::query()->first();

        if (! $company || ! $branch || ! $user) {
            return;
        }

        foreach (self::presetDefinitions() as $preset) {
            PrintProductTemplate::query()->updateOrCreate(
                ['company_id' => $company->id, 'code' => $preset['code']],
                [
                    ...$preset,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                    'artwork_required' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            );
        }
    }
}
