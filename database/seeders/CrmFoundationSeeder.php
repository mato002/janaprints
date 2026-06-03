<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use Illuminate\Database\Seeder;

class CrmFoundationSeeder extends Seeder
{
    private array $sources = [
        'Walk In', 'Referral', 'Website', 'Facebook', 'WhatsApp', 'Instagram', 'Existing Customer',
    ];

    private array $stages = [
        ['name' => 'New', 'slug' => 'new', 'sort_order' => 1],
        ['name' => 'Contacted', 'slug' => 'contacted', 'sort_order' => 2],
        ['name' => 'Qualified', 'slug' => 'qualified', 'sort_order' => 3],
        ['name' => 'Proposal Sent', 'slug' => 'proposal-sent', 'sort_order' => 4],
        ['name' => 'Negotiation', 'slug' => 'negotiation', 'sort_order' => 5],
        ['name' => 'Won', 'slug' => 'won', 'sort_order' => 6, 'is_won' => true],
        ['name' => 'Lost', 'slug' => 'lost', 'sort_order' => 7, 'is_lost' => true],
    ];

    public function run(): void
    {
        foreach (Company::query()->get() as $company) {
            foreach ($this->sources as $source) {
                LeadSource::query()->firstOrCreate(
                    ['company_id' => $company->id, 'name' => $source],
                    ['is_active' => true],
                );
            }

            foreach ($this->stages as $stage) {
                LeadStage::query()->firstOrCreate(
                    ['company_id' => $company->id, 'slug' => $stage['slug']],
                    [
                        'name' => $stage['name'],
                        'sort_order' => $stage['sort_order'],
                        'is_won' => $stage['is_won'] ?? false,
                        'is_lost' => $stage['is_lost'] ?? false,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
