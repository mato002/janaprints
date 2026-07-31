<?php

namespace Database\Seeders;

use App\Support\Demo\DesignerDeskDemoSeedService;
use Illuminate\Database\Seeder;

class DesignerDeskDemoSeeder extends Seeder
{
    public function run(): void
    {
        app(DesignerDeskDemoSeedService::class)->run($this->command);
    }
}
