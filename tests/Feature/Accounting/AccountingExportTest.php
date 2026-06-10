<?php

namespace Tests\Feature\Accounting;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\User;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountingExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_customer_invoices_index_renders_server_export_links(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['invoices.view']);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.invoices.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Export CSV'), false)
            ->assertSee('customer-invoices', false);
    }

    public function test_customer_invoices_export_downloads_csv(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['invoices.view']);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'EXP-INV-001',
            'invoice_date' => now(),
            'invoice_type' => CustomerInvoiceType::Standard,
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 100,
            'tax_amount' => 0,
            'total_amount' => 100,
            'balance_due' => 100,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.accounting.exports', ['listing' => 'customer-invoices', 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('EXP-INV-001', $response->streamedContent());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Company Admin');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }
}
