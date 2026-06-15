<?php

namespace Tests\Feature\Documents;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DocumentSetting;
use App\Models\User;
use App\Services\Documents\DocumentSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentSettingsCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $user->givePermissionTo($permission);
        }

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    public function test_document_settings_redirects_to_commercial_documents_workspace(): void
    {
        [, , $user] = $this->tenantUser(['documents.settings.view']);

        $this->actingAs($user)
            ->get(route('admin.documents.settings.index'))
            ->assertRedirect(route('admin.workspaces.administration.section', [
                'section' => 'commercial-documents',
                'tab' => 'document-settings',
            ]));
    }

    public function test_document_settings_page_is_accessible_in_workspace_frame(): void
    {
        [, , $user] = $this->tenantUser(['documents.settings.view']);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.documents.settings.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Commercial Document Settings'), false)
            ->assertSee(__('M-Pesa Paybill number'), false)
            ->assertDontSee(__('Back to Commercial Documents'), false);
    }

    public function test_commercial_documents_workspace_renders_embedded_settings_frame_src(): void
    {
        [, , $user] = $this->tenantUser(['documents.settings.view']);

        $this->actingAs($user)
            ->get(route('admin.workspaces.administration.section', [
                'section' => 'commercial-documents',
                'tab' => 'document-settings',
            ]))
            ->assertOk()
            ->assertSee(route('admin.documents.settings.index', ['embedded' => '1']), false)
            ->assertSee('module-workspace-content', false);
    }

    public function test_admin_can_save_document_payment_settings(): void
    {
        [$company, , $user] = $this->tenantUser(['documents.settings.view', 'documents.settings.edit']);

        $payload = [
            'payment_mpesa_paybill' => '888110',
            'payment_mpesa_account' => '553000',
            'payment_cheque_payable_to' => 'JANA PRINTS',
            'payment_bank_name' => 'NCBA Bank',
            'payment_bank_branch' => 'Nakuru Branch',
            'payment_bank_account_name' => 'Jana Prints',
            'payment_bank_account' => '1001798683',
        ];

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->from(route('admin.documents.settings.index', ['embedded' => '1']))
            ->put(route('admin.documents.settings.update', ['embedded' => '1']), $payload)
            ->assertRedirect(route('admin.documents.settings.index', ['embedded' => '1']));

        $this->assertDatabaseHas('document_settings', [
            'company_id' => $company->id,
            'key' => 'payment.mpesa_paybill',
            'value' => '888110',
        ]);

        $payment = app(DocumentSettingsService::class)->payment($company->id);

        $this->assertSame('888110', $payment['mpesa_paybill']);
        $this->assertSame('553000', $payment['mpesa_account']);
    }

    public function test_document_settings_fall_back_to_config_when_not_saved(): void
    {
        [$company] = $this->tenantUser(['documents.settings.view']);

        config([
            'documents.payment.mpesa_paybill' => '522522',
            'documents.payment.mpesa_account' => 'ACCT-99',
        ]);

        $payment = app(DocumentSettingsService::class)->payment($company->id);

        $this->assertSame('522522', $payment['mpesa_paybill']);
        $this->assertSame('ACCT-99', $payment['mpesa_account']);
    }

    public function test_reset_clears_custom_document_setting_value(): void
    {
        [$company, , $user] = $this->tenantUser(['documents.settings.view', 'documents.settings.edit']);

        DocumentSetting::query()->create([
            'company_id' => $company->id,
            'key' => 'footer.thanks',
            'group' => 'footer',
            'type' => 'string',
            'value' => 'Custom thanks',
            'fallback_value' => 'Thank you for choosing Jana Prints.',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->from(route('admin.documents.settings.index', ['embedded' => '1']))
            ->delete(route('admin.documents.settings.reset', 'footer.thanks').'?embedded=1')
            ->assertRedirect(route('admin.documents.settings.index', ['embedded' => '1']));

        $this->assertNull(
            DocumentSetting::query()
                ->where('company_id', $company->id)
                ->where('key', 'footer.thanks')
                ->value('value')
        );
    }
}
