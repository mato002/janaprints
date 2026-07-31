<?php

namespace Database\Seeders;

use App\Enums\VendorStatus;
use App\Models\Company;
use App\Models\Procurement\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductionVendorSeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, phone: string, email: string}>
     */
    private array $vendors = [
        [
            'code' => 'VEND-EPRINT',
            'name' => 'E-Print',
            'phone' => '0712000001',
            'email' => 'jobs@eprint.demo',
        ],
        [
            'code' => 'VEND-OFFSET-NBI',
            'name' => 'Offset Nbi',
            'phone' => '0712000002',
            'email' => 'jobs@offsetnbi.demo',
        ],
        [
            'code' => 'VEND-FREELANCE',
            'name' => 'Freelance',
            'phone' => '0712000003',
            'email' => 'jobs@freelance.demo',
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('vendors')) {
            return;
        }

        foreach (Company::query()->get() as $company) {
            foreach ($this->vendors as $profile) {
                Vendor::query()->updateOrCreate(
                    ['company_id' => $company->id, 'vendor_code' => $profile['code']],
                    [
                        'vendor_name' => $profile['name'],
                        'phone' => $profile['phone'],
                        'email' => $profile['email'],
                        'payment_terms' => 'Net 14',
                        'status' => VendorStatus::Active,
                        'is_production_vendor' => true,
                    ],
                );
            }
        }
    }
}
