<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientDemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('CLIENT_DEMO_EMAIL', 'client@janaprints.local');
        $name = env('CLIENT_DEMO_NAME', 'Demo Client');
        $password = env('CLIENT_DEMO_PASSWORD', env('DEMO_USER_PASSWORD', 'password'));

        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->firstOrFail();

        $customer = Customer::query()->updateOrCreate(
            ['company_id' => $company->id, 'customer_code' => 'CUST-DEMO-CLIENT'],
            [
                'branch_id' => $branch->id,
                'customer_type' => CustomerType::Corporate,
                'company_name' => 'Demo Client Company',
                'contact_person' => $name,
                'phone' => '+254700000001',
                'email' => $email,
                'city' => 'Nairobi',
                'status' => CustomerStatus::Active,
                'credit_limit' => 250000,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'company_id' => $company->id,
                'default_branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'employee_id' => null,
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $this->command?->info("Client portal demo ready: {$email} (login at /client/login)");
    }
}
