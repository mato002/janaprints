<?php

namespace Tests\Feature\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeliveryNoteBranchScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_for_tenant_scopes_delivery_notes_to_active_branch(): void
    {
        [$company, $branchA, $branchB, $customer, $noteA, $noteB] = $this->fixture();

        app()->instance(TenantContext::class, new TenantContext($company, $branchA, false));

        $visible = DeliveryNote::query()->forTenant()->pluck('id')->all();

        $this->assertContains($noteA->id, $visible);
        $this->assertNotContains($noteB->id, $visible);
    }

    public function test_delivery_note_index_excludes_other_branch_notes(): void
    {
        [$company, $branchA, $branchB, $customer, $noteA, $noteB, $user] = $this->fixture(withUser: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.dispatch.delivery-notes.index'))
            ->assertOk()
            ->assertSee($noteA->delivery_note_number, false)
            ->assertDontSee($noteB->delivery_note_number, false);
    }

    public function test_delivery_note_show_blocks_cross_branch_access(): void
    {
        [$company, $branchA, $branchB, $customer, $noteA, $noteB, $user] = $this->fixture(withUser: true);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($user)
            ->get(route('admin.dispatch.delivery-notes.show', $noteB))
            ->assertForbidden();
    }

    /**
     * @return array{
     *     0: Company,
     *     1: Branch,
     *     2: Branch,
     *     3: Customer,
     *     4: DeliveryNote,
     *     5: DeliveryNote,
     *     6?: User
     * }
     */
    protected function fixture(bool $withUser = false): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branchA = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $branchB = Branch::factory()->create([
            'company_id' => $company->id,
            'code' => 'BR-DN',
            'name' => 'Branch DN',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branchA->id,
        ]);

        $noteA = $this->createNote($company, $branchA, $customer, 'DN-HQ-001');
        $noteB = $this->createNote($company, $branchB, $customer, 'DN-BR-001');

        if (! $withUser) {
            return [$company, $branchA, $branchB, $customer, $noteA, $noteB];
        }

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branchA->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Dispatch Branch Tester '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.view']);
        $user->assignRole($role);

        return [$company, $branchA, $branchB, $customer, $noteA, $noteB, $user];
    }

    protected function createNote(Company $company, Branch $branch, Customer $customer, string $number): DeliveryNote
    {
        return DeliveryNote::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'delivery_note_number' => $number,
            'customer_id' => $customer->id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryNoteStatus::Draft,
        ]);
    }
}
