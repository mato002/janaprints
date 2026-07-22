<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Dashboard\OperatorDeskShortcutsPresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorDeskShortcutsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_elevated_admin_sales_desk_shortcut_opens_standalone_desk(): void
    {
        $admin = $this->userWithRole('Super Admin');

        $this->actingAs($admin)
            ->get(route('admin.sales.desk'))
            ->assertOk()
            ->assertSee(__('Sales desk'))
            ->assertSee(__('Quote requests'));

        $this->actingAs($admin)
            ->get(route('admin.production.floor', ['desk' => 1]))
            ->assertOk()
            ->assertSee(__('Production Floor'), false)
            ->assertDontSee(__('Operator mode'));
    }

    public function test_elevated_admin_sees_operator_desk_shortcuts_on_dashboard(): void
    {
        $admin = $this->userWithRole('Company Admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Operator Desks'), false)
            ->assertSee(__('Sales Desk'), false)
            ->assertSee(__('Designer Desk'), false)
            ->assertSee(__('Production Floor'), false)
            ->assertSee(__('Store Desk'), false)
            ->assertSee(route('admin.sales.desk'), false)
            ->assertSee(route('admin.artwork.desk'), false)
            ->assertSee(route('admin.production.floor', ['desk' => 1]), false)
            ->assertSee(route('admin.store.desk'), false);
    }

    public function test_elevated_admin_designer_desk_shortcut_opens_standalone_desk(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $admin->givePermissionTo('artwork.view');

        $this->actingAs($admin)
            ->get(route('admin.artwork.desk'))
            ->assertOk()
            ->assertSee(__('Designer Desk'), false);
    }

    public function test_sales_operator_does_not_see_operator_desk_shortcuts(): void
    {
        $sales = $this->userWithRole('Sales');

        $this->actingAs($sales)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.sales.desk'));

        $presenter = app(OperatorDeskShortcutsPresenter::class);

        $this->assertFalse($presenter->shouldShow($sales));
        $this->assertSame([], $presenter->forUser($sales));
    }

    public function test_shortcuts_are_filtered_by_permission(): void
    {
        $user = $this->staffUser();

        $shortcuts = app(OperatorDeskShortcutsPresenter::class)->forUser($user);

        $this->assertSame([], $shortcuts);
    }

    public function test_sales_desk_requires_both_customer_and_order_permissions(): void
    {
        $user = $this->staffUser();
        $user->syncPermissions(['crm.customers.create']);

        $keys = collect(app(OperatorDeskShortcutsPresenter::class)->forUser($user))
            ->pluck('key')
            ->all();

        $this->assertNotContains('sales', $keys);

        $user->givePermissionTo('sales_orders.create');

        $keys = collect(app(OperatorDeskShortcutsPresenter::class)->forUser($user))
            ->pluck('key')
            ->all();

        $this->assertContains('sales', $keys);
    }

    public function test_shortcuts_only_include_desks_the_user_can_access(): void
    {
        $user = $this->staffUser();
        $user->syncPermissions(['inventory.view']);

        $keys = collect(app(OperatorDeskShortcutsPresenter::class)->forUser($user))
            ->pluck('key')
            ->all();

        $this->assertSame(['store'], $keys);
    }

    protected function userWithRole(string $roleName): User
    {
        $user = $this->staffUser();
        $user->syncRoles([$roleName]);

        return $user;
    }

    protected function staffUser(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->firstOrFail();

        return User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }
}
