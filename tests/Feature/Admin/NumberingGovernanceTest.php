<?php

namespace Tests\Feature\Admin;

use App\Enums\DocumentType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\NumberingSequence;
use App\Models\User;
use App\Support\Platform\NumberGenerator;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NumberingGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
    }

    public function test_numbering_admin_page_is_accessible(): void
    {
        $user = $this->userWithPermissions(['settings.view']);

        $this->actingAs($user)
            ->get(route('admin.settings.numbering.index'))
            ->assertOk()
            ->assertSee(__('Document Numbering'))
            ->assertSee(__('Quotations'));
    }

    public function test_company_admin_can_update_numbering_sequences(): void
    {
        $user = $this->userWithPermissions(['settings.view', 'settings.manage']);
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.settings.numbering.update'), [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'sequences' => [
                    'quotation' => [
                        'prefix' => 'JANA',
                        'include_branch' => '1',
                        'include_year' => '1',
                        'include_month' => '0',
                        'padding' => '5',
                        'next_number' => '100',
                        'active' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.numbering.index', [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]));

        $sequence = NumberingSequence::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('document_type', 'quotation')
            ->first();

        $this->assertSame('JANA-{branch}-{type}-{year}-{number}', $sequence->format_template);
        $this->assertSame(100, $sequence->next_number);
    }

    public function test_number_generator_produces_unique_numbers(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $generator = app(NumberGenerator::class);

        $numbers = [];
        for ($i = 0; $i < 5; $i++) {
            $numbers[] = $generator->generate(DocumentType::Quotation, $company->id, $branch->id);
        }

        $this->assertCount(5, array_unique($numbers));
    }

    public function test_number_generator_is_concurrency_safe(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        NumberingSequence::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('document_type', DocumentType::Quotation->value)
            ->update(['next_number' => 1]);

        $results = [];

        for ($i = 0; $i < 10; $i++) {
            DB::transaction(function () use ($company, $branch, &$results) {
                $results[] = app(NumberGenerator::class)->generate(
                    DocumentType::Quotation,
                    $company->id,
                    $branch->id,
                );
            });
        }

        $this->assertCount(10, array_unique($results));
    }

    public function test_branch_sequences_are_isolated(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $hq = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $branchB = Branch::factory()->create([
            'company_id' => $company->id,
            'code' => 'BRB',
            'name' => 'Branch B',
        ]);

        $generator = app(NumberGenerator::class);

        NumberingSequence::query()->updateOrCreate(
            ['company_id' => $company->id, 'branch_id' => $branchB->id, 'document_type' => DocumentType::Customer->value],
            ['format_template' => 'JANA-BRB-CUST-{year}-{number}', 'next_number' => 1, 'padding' => 5, 'include_year' => true, 'include_branch_code' => true],
        );

        $hqNumber = $generator->generate(DocumentType::Customer, $company->id, $hq->id);
        $branchNumber = $generator->generate(DocumentType::Customer, $company->id, $branchB->id);

        $this->assertStringContainsString('HQ', $hqNumber);
        $this->assertStringContainsString('BRB', $branchNumber);
        $this->assertNotSame($hqNumber, $branchNumber);
    }

    public function test_year_rollover_resets_sequence(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $settings = app(SystemSettingsService::class);
        $generator = app(NumberGenerator::class);

        NumberingSequence::query()
            ->where('company_id', $company->id)
            ->where('branch_id', $branch->id)
            ->where('document_type', DocumentType::SalesOrder->value)
            ->update(['next_number' => 250, 'include_year' => true]);

        $settings->set('numbering.last_year.sales_order', now()->year - 1, $company->id, $branch->id, 'integer');

        $number = $generator->generate(DocumentType::SalesOrder, $company->id, $branch->id);

        $this->assertStringEndsWith('00001', $number);
        $this->assertStringContainsString('SO', $number);
        $this->assertSame(2, NumberingSequence::query()
            ->where('document_type', DocumentType::SalesOrder->value)
            ->where('branch_id', $branch->id)
            ->value('next_number'));
    }

    public function test_inactive_sequence_cannot_generate(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $generator = app(NumberGenerator::class);

        $generator->setActive(DocumentType::Invoice, false, $company->id, $branch->id);

        $this->expectException(ValidationException::class);
        $generator->generate(DocumentType::Invoice, $company->id, $branch->id);
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithPermissions(array $permissions): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Numbering Tester', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
