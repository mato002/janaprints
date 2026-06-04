<?php

namespace Database\Factories\Procurement;

use App\Enums\VendorStatus;
use App\Enums\VendorType;
use App\Models\Company;
use App\Models\Procurement\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vendor> */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'vendor_code' => 'VEND-'.fake()->unique()->numerify('#####'),
            'vendor_name' => fake()->company(),
            'vendor_type' => VendorType::Supplier,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'status' => VendorStatus::Active,
        ];
    }
}
