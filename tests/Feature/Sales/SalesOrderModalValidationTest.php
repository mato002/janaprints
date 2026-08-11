<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesOrderModalValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_modal_create_from_quotation_without_selection_returns_visible_validation_error(): void
    {
        [$user] = $this->salesUser();

        $createUrl = route('admin.sales-orders.create');

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->post(route('admin.sales-orders.store'), [
                '_erp_modal' => '1',
                '_erp_modal_form_url' => $createUrl,
                '_erp_modal_return' => $createUrl,
                'entry_mode' => 'quotation',
            ]);

        $response->assertStatus(422);
        $response->assertSee('data-erp-form-modal-panel', false);
        $response->assertSee('data-erp-validation-message', false);
        $response->assertSee(__('The quotation field is required.'), false);
    }

    public function test_modal_create_from_quotation_with_desk_return_url_still_shows_validation(): void
    {
        [$user] = $this->salesUser();

        $createUrl = route('admin.sales-orders.create');
        $deskUrl = route('admin.sales.desk', ['view' => 'orders']);

        $response = $this->actingAs($user)
            ->withHeader('Turbo-Frame', 'erp-form-modal')
            ->from($deskUrl)
            ->post(route('admin.sales-orders.store'), [
                '_erp_modal' => '1',
                '_erp_modal_form_url' => $createUrl,
                '_erp_modal_return' => $deskUrl,
                'entry_mode' => 'quotation',
            ]);

        $response->assertStatus(422);
        $response->assertSee('data-erp-validation-message', false);
        $response->assertSee(__('The quotation field is required.'), false);
    }

    /**
     * @return array{0: User}
     */
    protected function salesUser(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$user];
    }
}
