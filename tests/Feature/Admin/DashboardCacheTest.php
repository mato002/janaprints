<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Support\Platform\PlatformCacheService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Cache::flush();
    }

    public function test_executive_dashboard_uses_tenant_scoped_cache_key(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $branchA = Branch::factory()->create(['company_id' => $companyA->id]);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        $cache = app(PlatformCacheService::class);

        $cache->remember('dashboard', "{$companyA->id}:{$branchA->id}", fn () => ['marker' => 'company-a'], 60);
        $cache->remember('dashboard', "{$companyB->id}:{$branchB->id}", fn () => ['marker' => 'company-b'], 60);

        $this->assertSame('company-a', Cache::get('platform:dashboard:'.$companyA->id.':'.$branchA->id)['marker']);
        $this->assertSame('company-b', Cache::get('platform:dashboard:'.$companyB->id.':'.$branchB->id)['marker']);
        $this->assertNotSame(
            Cache::get('platform:dashboard:'.$companyA->id.':'.$branchA->id),
            Cache::get('platform:dashboard:'.$companyB->id.':'.$branchB->id),
        );
    }
}
