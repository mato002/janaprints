<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_users_export_supports_csv_excel_and_pdf(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'name' => 'Export Test User',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $this->actingAs($user)
                ->get(route('admin.users.export', ['format' => $format]));

            $response->assertOk()->assertHeader('content-disposition');

            if ($format === 'pdf') {
                $response->assertHeader('content-type', 'application/pdf');
                $this->assertStringStartsWith('%PDF', $response->streamedContent());
            }
        }
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Company Admin', 'web')->syncPermissions(['users.view']);
        $user->assignRole('Company Admin');

        return [$company, $branch, $user];
    }
}
