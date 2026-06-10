<?php

namespace Tests\Feature\Inventory;

use App\Enums\VirtualWarehouseRole;
use App\Models\Company;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\VirtualWarehouseResolverService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualWarehouseResolverServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VirtualWarehouseResolverService $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        $this->resolver = app(VirtualWarehouseResolverService::class);
    }

    public function test_resolves_all_seeded_virtual_roles(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->resolver->ensureDefaults($company->id);

        $this->assertSame('VIRTUAL-RAW', $this->resolver->rawMaterials($company->id)?->code);
        $this->assertSame('VIRTUAL-WIP', $this->resolver->workInProgress($company->id)?->code);
        $this->assertSame('VIRTUAL-FG', $this->resolver->finishedGoods($company->id)?->code);
        $this->assertSame('VIRTUAL-TRANSIT', $this->resolver->inTransit($company->id)?->code);
        $this->assertSame('VIRTUAL-QUARANTINE', $this->resolver->quarantine($company->id)?->code);
    }

    public function test_resolve_by_role_returns_active_virtual_warehouse(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->resolver->ensureDefaults($company->id);

        $warehouse = $this->resolver->resolveByRole($company->id, VirtualWarehouseRole::Wip);

        $this->assertNotNull($warehouse);
        $this->assertTrue($warehouse->is_virtual);
        $this->assertSame(VirtualWarehouseRole::Wip, $warehouse->virtual_role);
    }

    public function test_ensure_defaults_does_not_duplicate_records(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->resolver->ensureDefaults($company->id);
        $this->resolver->ensureDefaults($company->id);

        $this->assertSame(
            count(VirtualWarehouseRole::seededRoles()),
            Warehouse::query()->where('company_id', $company->id)->virtual()->count(),
        );
    }
}
