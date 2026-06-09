<?php

namespace Database\Seeders;

use App\Support\Demo\OperationalDemoSeedService;
use Illuminate\Database\Seeder;

class OperationalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding 3 months of operational demo data…');

        app(OperationalDemoSeedService::class)->run($this->command);

        $this->call([
            InboxDemoSeeder::class,
        ]);

        $this->command?->newLine();
        $this->command?->info('Operational demo seed complete.');
        $this->command?->line('  Log in: admin@janaprints.local / password (or your DEMO_USER_PASSWORD)');
        $this->command?->line('  Sales: sales@janaprints.local');
        $this->command?->line('  Client portal: client.demo@janaprints.local');
    }
}
