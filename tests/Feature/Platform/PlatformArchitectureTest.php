<?php

namespace Tests\Feature\Platform;

use App\Enums\ApprovalRuleType;
use App\Enums\DocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_platform_configuration_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('system_settings'));
        $this->assertTrue(Schema::hasTable('form_settings'));
        $this->assertTrue(Schema::hasTable('form_field_settings'));
        $this->assertTrue(Schema::hasTable('numbering_sequences'));
        $this->assertTrue(Schema::hasTable('approval_rules'));
    }

    public function test_tenant_status_indexes_exist_on_quotations(): void
    {
        $indexes = collect(Schema::getIndexes('quotations'))->pluck('name');

        $this->assertTrue($indexes->contains('quotations_tenant_status_idx'));
        $this->assertTrue($indexes->contains('quotations_company_date_idx'));
    }

    public function test_activity_logs_has_company_index(): void
    {
        $indexes = collect(Schema::getIndexes('activity_logs'))->pluck('name');

        $this->assertTrue($indexes->contains('activity_logs_company_created_idx'));
        $this->assertTrue(Schema::hasColumn('activity_logs', 'company_id'));
    }

    public function test_numbering_service_generates_unique_sequential_numbers(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $service = app(NumberingService::class);

        $first = $service->next(DocumentType::Quotation, $company->id, $branch->id);
        $second = $service->next(DocumentType::Quotation, $company->id, $branch->id);

        $this->assertNotSame($first, $second);
        $this->assertStringContainsString('JANA', $first);
        $this->assertStringContainsString('HQ', $first);
        $this->assertStringContainsString('QUOTE', $first);
        $this->assertSame(3, NumberingSequence::query()->where('document_type', DocumentType::Quotation->value)->value('next_number'));
    }

    public function test_system_settings_fallback_to_company_defaults(): void
    {
        $settings = app(SystemSettingsService::class);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->assertSame(30, $settings->get('quotation_validity_days', null, $company->id));
        $this->assertSame('Net 30', $settings->get('default_payment_terms', null, $company->id));
        $this->assertSame(99, $settings->get('missing_key', 99, $company->id));
    }

    public function test_form_settings_retrieval(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $forms = app(FormSettingsService::class);

        $this->assertTrue($forms->isRequired('quotation', 'customer_id', $company->id));
        $this->assertFalse($forms->isRequired('quotation', 'lead_id', $company->id));
        $this->assertTrue($forms->isVisible('quotation', 'notes', $company->id));
    }

    public function test_approval_rules_service_detects_thresholds(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $rules = app(ApprovalRulesService::class);

        $this->assertTrue($rules->requiresApproval(
            ApprovalRuleType::DiscountApproval,
            null,
            12,
            $company->id,
            $branch->id,
        ));

        $this->assertFalse($rules->requiresApproval(
            ApprovalRuleType::DiscountApproval,
            null,
            3,
            $company->id,
            $branch->id,
        ));
    }

    public function test_queue_configuration_is_platform_ready(): void
    {
        $this->assertIsArray(config('platform.queues'));
        $this->assertArrayHasKey('reports', config('platform.queues'));
        $this->assertArrayHasKey('database', config('queue.connections'));
        $this->assertSame('database', config('queue.connections.database.driver'));
    }

    public function test_admin_layout_includes_turbo_main_frame(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('id="erp-main"', false);
        $response->assertSee('data-turbo-action="advance"', false);
        $response->assertSee('data-turbo-frame="erp-main"', false);
        $response->assertSee('id="erp-route-meta"', false);
    }

    public function test_navigation_menu_is_cached_per_user_context(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');
        $user->load('roles');

        $roleKey = $user->roles->pluck('name')->sort()->implode('|');
        $cacheKey = "{$user->id}:{$company->id}:{$branch->id}:{$roleKey}";

        app(\App\Support\Platform\PlatformCacheService::class)->forget('navigation', $cacheKey);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

        $this->assertNotNull(
            \Illuminate\Support\Facades\Cache::get("platform:navigation:{$cacheKey}"),
        );
    }

    public function test_roles_index_is_paginated(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Company Admin', 'web');
        $role->syncPermissions(['roles.view']);
        $user->assignRole('Company Admin');

        $response = $this->actingAs($user)->get(route('admin.access-control.roles'));

        $response->assertOk();
        $response->assertViewHas('roles', fn ($roles) => method_exists($roles, 'links'));
    }
}
