<?php

namespace Tests\Feature\Sales;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationModalValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_modal_validation_failure_returns_form_panel_with_errors(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
        $user = $this->salesUser($company, $branch, ['quotations.create']);
        $createUrl = route('admin.quotations.create');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.quotations.store'), [
                '_erp_modal' => '1',
                '_erp_modal_return' => $createUrl,
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'currency' => 'KES',
                'items' => [
                    [
                        'item_type' => 'product',
                        'item_name' => '',
                        'quantity' => 1,
                        'unit_price' => 100,
                    ],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertSee('data-erp-form-modal-panel', false);
        $response->assertSee('data-erp-validation-errors', false);
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
