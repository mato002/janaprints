<?php

namespace Tests\Unit\Support\AccessControl;

use App\Support\AccessControl\PermissionCatalog;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    public function test_workspace_payload_builds_module_columns_with_extras(): void
    {
        $catalog = app(PermissionCatalog::class);

        $inventoryColumns = $catalog->columnsForModule('inventory');
        $columnKeys = array_column($inventoryColumns, 'key');

        $this->assertContains('view', $columnKeys);
        $this->assertContains('inventory.receive', $columnKeys);
        $this->assertContains('inventory.transfer', $columnKeys);

        $payload = $catalog->workspacePayload(['inventory.view', 'inventory.receive']);

        $this->assertSame(['inventory.view', 'inventory.receive'], $payload['granted']);
        $this->assertNotEmpty($payload['modules']);

        $inventory = collect($payload['modules'])->firstWhere('key', 'inventory');
        $this->assertNotNull($inventory);
        $this->assertSame('inventory.receive', $inventory['rows'][0]['cells']['inventory.receive']);
    }

    public function test_role_summary_stats_count_modules_and_permissions(): void
    {
        $catalog = app(PermissionCatalog::class);

        $stats = $catalog->roleSummaryStats([
            'crm.customers.view',
            'crm.leads.view',
            'inventory.view',
            'inventory.receive',
        ]);

        $this->assertSame(2, $stats['modules_enabled']);
        $this->assertSame(4, $stats['permissions_enabled']);
    }

    public function test_module_coverage_lists_enabled_modules(): void
    {
        $catalog = app(PermissionCatalog::class);

        $coverage = $catalog->moduleCoverage(['crm.customers.view', 'inventory.view']);

        $enabled = collect($coverage)->where('enabled', true)->pluck('key')->all();

        $this->assertContains('crm', $enabled);
        $this->assertContains('inventory', $enabled);
        $this->assertNotContains('sales', $enabled);
    }
}
