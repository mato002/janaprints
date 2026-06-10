<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TableExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_table_export_supports_csv_excel_and_pdf(): void
    {
        $user = $this->adminUser();

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $this->actingAs($user)
                ->postJson(route('admin.exports.table'), [
                    'format' => $format,
                    'basename' => 'sample-list',
                    'title' => 'Sample List',
                    'headers' => ['Name', 'Status'],
                    'rows' => [
                        ['Alpha Co', 'Active'],
                        ['Beta Co', 'Inactive'],
                    ],
                ]);

            $response->assertOk()
                ->assertHeader('content-disposition');

            if ($format === 'pdf') {
                $response->assertHeader('content-type', 'application/pdf');
                $this->assertStringStartsWith('%PDF', $response->streamedContent());
            }

            if ($format === 'csv') {
                $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
            }

            if ($format === 'excel') {
                $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
            }
        }
    }

    public function test_table_export_accepts_form_payload(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)
            ->post(route('admin.exports.table'), [
                '_token' => csrf_token(),
                'format' => 'pdf',
                'basename' => 'users',
                'title' => 'Users',
                'headers' => json_encode(['Name', 'Email']),
                'rows' => json_encode([['Jane Doe', 'jane@example.com']]),
            ]);

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    protected function adminUser(): User
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Company Admin', 'web')->givePermissionTo([]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
