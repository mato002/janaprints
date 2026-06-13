<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Documents\Presenters\QuotationDocumentPresenter;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create(['code' => 'JANA']);
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id, 'code' => 'HQ']);
        $this->user = $this->salesUser($this->company, $this->branch, ['quotations.view']);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_quotation_document_page_loads(): void
    {
        $quotation = $this->makeQuotation();

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotation))
            ->assertOk()
            ->assertSee($quotation->quotation_number)
            ->assertSee('QUOTATION');
    }

    public function test_quotation_pdf_download_works(): void
    {
        $quotation = $this->makeQuotation();

        $response = $this->actingAs($this->user)
            ->get(route('admin.quotations.document.pdf', $quotation));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_missing_customer_fields_do_not_break_template(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'company_name' => 'Minimal Customer',
            'contact_person' => null,
            'phone' => null,
            'email' => null,
            'physical_address' => null,
            'postal_address' => null,
            'city' => null,
            'status' => CustomerStatus::Active,
        ]);

        $quotation = $this->makeQuotation($customer);

        $document = app(QuotationDocumentPresenter::class)->present($quotation);

        $this->assertSame('Minimal Customer', $document['customer']['company']);

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotation))
            ->assertOk()
            ->assertSee('Minimal Customer');
    }

    public function test_empty_line_items_show_safe_empty_state(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => Customer::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'status' => CustomerStatus::Active,
            ])->id,
            'prepared_by' => $this->user->id,
            'status' => QuotationStatus::Draft,
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotation))
            ->assertOk()
            ->assertSee('No line items on this document', false);
    }

    public function test_totals_render_correctly(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => Customer::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'status' => CustomerStatus::Active,
            ])->id,
            'prepared_by' => $this->user->id,
            'currency' => 'KES',
            'subtotal' => 1000,
            'tax_amount' => 160,
            'discount_amount' => 50,
            'total_amount' => 1110,
        ]);

        $quotation->items()->create([
            'item_type' => 'product',
            'item_name' => 'Brochures',
            'quantity' => 100,
            'unit_price' => 10,
            'discount' => 0,
            'tax_rate' => 16,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotation))
            ->assertOk()
            ->assertSee('KES 1,000.00')
            ->assertSee('KES 160.00')
            ->assertSee('KES 50.00')
            ->assertSee('KES 1,110.00');
    }

    public function test_status_badge_renders(): void
    {
        $quotation = $this->makeQuotation();
        $quotation->update(['status' => QuotationStatus::PendingApproval]);

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotation))
            ->assertOk()
            ->assertSee('Pending');
    }

    public function test_unauthorized_user_cannot_access_document(): void
    {
        $companyB = Company::factory()->create(['code' => 'QB']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'status' => CustomerStatus::Active,
        ]);

        $quotationB = Quotation::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'prepared_by' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.quotations.document', $quotationB))
            ->assertForbidden();
    }

    protected function makeQuotation(?Customer $customer = null): Quotation
    {
        $customer ??= Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'company_name' => 'Acme Industries',
            'email' => 'billing@acme.test',
            'status' => CustomerStatus::Active,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $this->user->id,
            'status' => QuotationStatus::Draft,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'discount_amount' => 0,
            'total_amount' => 1160,
        ]);

        $quotation->items()->create([
            'item_type' => 'product',
            'item_name' => 'Business Cards',
            'quantity' => 500,
            'unit_price' => 2,
            'discount' => 0,
            'tax_rate' => 16,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        return $quotation;
    }

    protected function salesUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return $user;
    }
}
