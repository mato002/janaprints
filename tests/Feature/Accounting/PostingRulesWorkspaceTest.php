<?php

namespace Tests\Feature\Accounting;

use App\Models\Accounting\GlAccount;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\Posting\PostingRuleValidationResult;
use App\Support\Accounting\Posting\PostingRuleValidationService;
use App\Support\Accounting\Posting\PostingRuleWorkspacePresenter;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PostingRulesWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $accountant;

    protected User $auditor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();

        $this->accountant = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->accountant->assignRole('Accountant');

        $this->auditor = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->auditor->assignRole('Viewer');

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_posting_rules_index_requires_permission(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.accounting.posting.rules.index'))
            ->assertForbidden();
    }

    public function test_filter_option_labels_are_strings(): void
    {
        $workspace = app(PostingRuleWorkspacePresenter::class)->buildIndex(request());

        foreach ($workspace['filterOptions']['validation_statuses'] as $option) {
            $this->assertIsString($option['label'], 'Expected string label, got '.gettype($option['label']));
        }
    }

    public function test_rule_validation_labels_are_strings(): void
    {
        $workspace = app(PostingRuleWorkspacePresenter::class)->buildIndex(request());
        $rule = $workspace['rules']->first();
        $validation = $workspace['validations'][$rule->id];

        $this->assertIsString($validation->label());
        $this->assertIsString($rule->module->label());
    }

    public function test_posting_rules_index_shows_dashboard_for_accountant(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('admin.accounting.posting.rules.index'));

        $response->assertOk();
        $response->assertSee(__('Total rules'));
        $response->assertSee(__('Validation errors'));
        $response->assertSee(__('Rules by module'));
        $response->assertSee('supplier_bill.posted');
    }

    public function test_posting_rules_show_returns_rule_detail_json(): void
    {
        $rule = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->where('event_code', 'supplier_bill.posted')
            ->firstOrFail();

        $response = $this->actingAs($this->accountant)
            ->getJson(route('admin.accounting.posting.rules.show', $rule));

        $response->assertOk();
        $response->assertJsonPath('rule.event_code', 'supplier_bill.posted');
        $response->assertJsonStructure([
            'rule' => [
                'name',
                'validation' => ['status', 'issues'],
                'account_mappings',
                'journal_preview' => ['debit_lines', 'credit_lines'],
                'workflow' => ['steps'],
                'audit',
            ],
        ]);
    }

    public function test_auditor_can_view_rules_with_audit_permission_only(): void
    {
        Permission::findOrCreate('accounting.posting_rules.audit', 'web');
        $this->auditor->givePermissionTo('accounting.posting_rules.audit');

        $rule = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        $this->actingAs($this->auditor)
            ->get(route('admin.accounting.posting.rules.index'))
            ->assertOk();

        $this->actingAs($this->auditor)
            ->getJson(route('admin.accounting.posting.rules.show', $rule))
            ->assertOk()
            ->assertJsonStructure(['rule' => ['audit']]);
    }

    public function test_validation_flags_missing_template_as_broken(): void
    {
        $rule = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->with('template')
            ->firstOrFail();

        $rule->setRelation('template', null);

        $result = app(PostingRuleValidationService::class)->validate($rule);

        $this->assertSame(PostingRuleValidationResult::STATUS_BROKEN, $result->status);
    }

    public function test_validation_flags_locked_account_as_broken(): void
    {
        $rule = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->with('template.lines')
            ->firstOrFail();

        $line = $rule->template->lines->first();

        if (! $line?->gl_account_id) {
            $this->markTestSkipped('No fixed-account line in seeded posting template.');
        }

        GlAccount::query()->whereKey($line->gl_account_id)->update(['status' => 'locked']);

        $result = app(PostingRuleValidationService::class)->validate($rule->fresh(['template.lines.glAccount']));

        $this->assertSame(PostingRuleValidationResult::STATUS_BROKEN, $result->status);
    }

    public function test_module_filter_narrows_index_results(): void
    {
        $module = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->firstOrFail()
            ->module;

        $response = $this->actingAs($this->accountant)
            ->get(route('admin.accounting.posting.rules.index', ['module' => $module->value]));

        $response->assertOk();

        PostingRule::query()
            ->where('company_id', $this->company->id)
            ->where('module', '!=', $module)
            ->pluck('event_code')
            ->each(fn (string $eventCode) => $response->assertDontSee($eventCode, false));
    }

    public function test_presenter_includes_journal_usage_stats(): void
    {
        $rule = PostingRule::query()
            ->where('company_id', $this->company->id)
            ->firstOrFail();

        $period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();

        Journal::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'fiscal_year_id' => $period->fiscal_year_id,
            'accounting_period_id' => $period->id,
            'journal_number' => 'TEST-JNL-9001',
            'journal_date' => $period->start_date,
            'entry_type' => 'system',
            'status' => 'posted',
            'posting_rule_id' => $rule->id,
            'posting_template_id' => $rule->posting_template_id,
            'total_debit' => 1000,
            'total_credit' => 1000,
            'posted_at' => now(),
            'posted_by' => $this->accountant->id,
            'created_by' => $this->accountant->id,
        ]);

        $detail = app(PostingRuleWorkspacePresenter::class)->buildRuleDetail($rule, true);

        $this->assertSame(1, $detail['audit']['posted_journals']);
        $this->assertSame('1,000.00', $detail['audit']['total_amount_posted']);
    }
}
