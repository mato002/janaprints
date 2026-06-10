<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintingIntelligencePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_ink_intelligence_loads_with_bounded_query_count(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee(__('Ink Intelligence'));

        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(80, $queryCount, "Expected bounded queries, got {$queryCount}");
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function userWith(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
        $user->assignRole('Storekeeper');

        return [$company, $branch, $user];
    }
}
