<?php

namespace Database\Seeders;

use App\Support\Demo\ProductionCommandCentreDemoSeedService;
use Illuminate\Database\Seeder;

class ProductionCommandCentreDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding production command centre & operational register showcase data…');

        app(ProductionCommandCentreDemoSeedService::class)->run($this->command);

        $this->command?->newLine();
        $this->command?->info('Production showcase seed complete.');
        $this->command?->line('  Work Center Queue (all):     /admin/production/queue');
        $this->command?->line('  Digital command centre:      /admin/production/queue/department/digital');
        $this->command?->line('  Offset command centre:       /admin/production/queue/department/offset');
        $this->command?->line('  Large format command centre: /admin/production/queue/department/large_format');
        $this->command?->line('  Finishing command centre:    /admin/production/queue/department/finishing');
        $this->command?->line('  Outsource command centre:    /admin/production/queue/department/outsource');
        $this->command?->line('  Operational registers:       /admin/reports/operational-registers?register=daily_sales&preset=today');
        $this->command?->line('  Log in as admin@janaprints.local and select company JANA / branch HQ.');
    }
}
