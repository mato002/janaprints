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
        $this->command?->line('  Operator Floor (Digital):  /admin/production/floor?view=queue&department=digital');
        $this->command?->line('  Operator Floor (Offset):   /admin/production/floor?view=queue&department=offset');
        $this->command?->line('  Operator Floor (Outsource):/admin/production/floor?view=queue&department=outsource');
        $this->command?->line('  Log in as production@janaprints.local (operator) or admin@janaprints.local.');
    }
}
