<?php

namespace Tests\Feature\Commercial;

use App\Enums\CommercialComplaintPriority;
use App\Enums\CommercialComplaintSource;
use App\Enums\CommercialComplaintStatus;
use App\Models\Branch;
use App\Models\Commercial\CommercialComplaint;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialComplaintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_complaint_create_and_resolve_workflow(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.complaints.view', 'commercial.complaints.create', 'commercial.complaints.resolve',
        ]);

        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.complaints.store'), [
            'customer_id' => $customer->id,
            'subject' => 'Late delivery',
            'description' => 'Order arrived two days late.',
            'source' => CommercialComplaintSource::Phone->value,
            'priority' => CommercialComplaintPriority::High->value,
        ])->assertRedirect();

        $complaint = CommercialComplaint::query()->first();
        $this->assertNotNull($complaint);
        $this->assertEquals(CommercialComplaintStatus::Open, $complaint->status);

        $this->actingAs($user)->post(route('admin.commercial.complaints.resolve', $complaint), [
            'resolution_notes' => 'Issued credit note via accounting.',
        ])->assertRedirect();

        $complaint->refresh();
        $this->assertEquals(CommercialComplaintStatus::Resolved, $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
