<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\MasterDataValue;
use Illuminate\Database\Seeder;

class MasterDataFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        $defaults = [
            'customer_types' => [
                ['code' => 'corporate', 'name' => 'Corporate', 'sort_order' => 1],
                ['code' => 'individual', 'name' => 'Individual', 'sort_order' => 2],
            ],
            'payment_terms' => [
                ['code' => 'net_30', 'name' => 'Net 30', 'sort_order' => 1],
                ['code' => 'net_15', 'name' => 'Net 15', 'sort_order' => 2],
                ['code' => 'cod', 'name' => 'Cash on Delivery', 'sort_order' => 3],
            ],
            'lead_sources' => [
                ['code' => 'website', 'name' => 'Website', 'sort_order' => 1],
                ['code' => 'referral', 'name' => 'Referral', 'sort_order' => 2],
                ['code' => 'walk_in', 'name' => 'Walk-in', 'sort_order' => 3],
            ],
            'units_of_measure' => [
                ['code' => 'pcs', 'name' => 'Pieces', 'sort_order' => 1],
                ['code' => 'kg', 'name' => 'Kilograms', 'sort_order' => 2],
                ['code' => 'ream', 'name' => 'Ream', 'sort_order' => 3],
            ],
            'delivery_methods' => [
                ['code' => 'pickup', 'name' => 'Customer Pickup', 'sort_order' => 1],
                ['code' => 'courier', 'name' => 'Courier Delivery', 'sort_order' => 2],
            ],
            'payment_methods' => [
                ['code' => 'cash', 'name' => 'Cash', 'sort_order' => 1],
                ['code' => 'bank_transfer', 'name' => 'Bank Transfer', 'sort_order' => 2],
                ['code' => 'card', 'name' => 'Card', 'sort_order' => 3],
            ],
        ];

        foreach ($defaults as $categoryKey => $rows) {
            foreach ($rows as $row) {
                MasterDataValue::query()->firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'category_key' => $categoryKey,
                        'code' => $row['code'],
                    ],
                    [
                        'name' => $row['name'],
                        'sort_order' => $row['sort_order'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
