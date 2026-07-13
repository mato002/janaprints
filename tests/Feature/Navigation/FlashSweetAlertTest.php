<?php

namespace Tests\Feature\Navigation;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSweetAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_alerts_partial_emits_sweetalert_flash_markers(): void
    {
        session()->flash('status', 'Saved successfully.');
        session()->flash('error', 'Something failed.');
        session()->flash('warning', 'Be careful.');
        session()->flash('info', 'Heads up.');

        $html = view('admin.partials.alerts')->render();

        $this->assertStringContainsString('data-erp-flash-status', $html);
        $this->assertStringContainsString('Saved successfully.', $html);
        $this->assertStringContainsString('data-erp-flash-error', $html);
        $this->assertStringContainsString('Something failed.', $html);
        $this->assertStringContainsString('data-erp-flash-warning', $html);
        $this->assertStringContainsString('data-erp-flash-info', $html);
    }

    public function test_success_flash_alias_uses_status_marker(): void
    {
        session()->flash('success', 'Created.');

        $html = view('admin.partials.alerts')->render();

        $this->assertStringContainsString('data-erp-flash-status', $html);
        $this->assertStringContainsString('Created.', $html);
    }

    public function test_workspace_shell_redirect_preserves_status_flash(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $user = $this->companyAdmin();

        $this->actingAs($user);
        session()->flash('status', 'Saved successfully.');

        $response = $this->get(route('admin.crm.customers.index'));

        if ($response->isRedirect()) {
            $follow = $this->get($response->headers->get('Location'));
            $follow->assertOk();
            $follow->assertSee('data-erp-flash-status', false);
            $follow->assertSee('Saved successfully.', false);

            return;
        }

        $response->assertOk();
        $response->assertSee('data-erp-flash-status', false);
        $response->assertSee('Saved successfully.', false);
    }

    public function test_compact_inbox_page_still_emits_flash_markers(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $user = $this->companyAdmin();

        $this->actingAs($user);
        session()->flash('status', 'Message sent.');

        $response = $this->get(route('admin.communications.inbox.index'));

        if ($response->isRedirect()) {
            $response = $this->get($response->headers->get('Location'));
        }

        $response->assertOk()
            ->assertSee('data-erp-flash-status', false)
            ->assertSee('Message sent.', false);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
