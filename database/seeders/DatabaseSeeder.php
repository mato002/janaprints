<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OrganizationFoundationSeeder::class,
            ProductionFoundationSeeder::class,
            InventoryFoundationSeeder::class,
            CrmFoundationSeeder::class,
            BootstrapAdminSeeder::class,
            DemoUsersSeeder::class,
            PlatformConfigurationSeeder::class,
            GlAccountTypeSeeder::class,
            JanaPrintsChartOfAccountsSeeder::class,
            JanaPrintsAccountingPeriodsSeeder::class,
            JanaPrintsPostingEngineSeeder::class,
            JanaPrintsPosPostingSeeder::class,
            JanaPrintsTaxSeeder::class,
        ]);
    }
}
