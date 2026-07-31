<?php

use App\Enums\ArtworkPriority;
use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtworkClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_designer_can_claim_open_job_from_desk(): void
    {
        $designer = $this->userWithRole('Designer');
        $companyId = $designer->company_id;
        $branchId = $designer->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-CLAIM-001',
            'title' => 'Open Claim Job',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Requested,
            'assigned_designer_id' => null,
            'requested_by' => $designer->id,
            'current_version' => 0,
        ]);

        $this->assertTrue($designer->can('claim', $request));

        $this->actingAs($designer)
            ->post(route('admin.artwork.claim', $request), [
                'from' => 'designer-desk',
                '_erp_modal' => '1',
                '_erp_modal_return' => route('admin.artwork.desk', ['request' => $request->public_id]),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $request->refresh();
        $this->assertSame($designer->id, $request->assigned_designer_id);
        $this->assertSame(ArtworkRequestStatus::InDesign, $request->status);
    }

    public function test_super_admin_can_claim_open_job(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $companyId = Company::query()->where('code', 'JANA')->value('id');
        $branchId = Branch::query()->where('code', 'HQ')->value('id');
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-CLAIM-SA',
            'title' => 'SA Claim Job',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Requested,
            'assigned_designer_id' => null,
            'requested_by' => $admin->id,
            'current_version' => 0,
        ]);

        $this->assertTrue($admin->can('claim', $request));

        $this->actingAs($admin)
            ->post(route('admin.artwork.claim', $request), [
                'from' => 'designer-desk',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $request->refresh();
        $this->assertSame($admin->id, $request->assigned_designer_id);
    }

    public function test_claim_denied_when_already_assigned_to_someone_else(): void
    {
        $designer = $this->userWithRole('Designer');
        $other = $this->userWithRole('Designer');
        $companyId = $designer->company_id;
        $branchId = $designer->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-CLAIM-TAKEN',
            'title' => 'Taken Job',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::InDesign,
            'assigned_designer_id' => $other->id,
            'requested_by' => $designer->id,
            'current_version' => 0,
        ]);

        $this->assertFalse($designer->can('claim', $request));

        $this->actingAs($designer)
            ->post(route('admin.artwork.claim', $request), [
                'from' => 'designer-desk',
            ])
            ->assertForbidden();
    }

    public function test_claim_is_idempotent_when_already_mine(): void
    {
        $designer = $this->userWithRole('Designer');
        $companyId = $designer->company_id;
        $branchId = $designer->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-CLAIM-MINE',
            'title' => 'Already Mine',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::InDesign,
            'assigned_designer_id' => $designer->id,
            'requested_by' => $designer->id,
            'current_version' => 0,
        ]);

        $this->actingAs($designer)
            ->post(route('admin.artwork.claim', $request), [
                'from' => 'designer-desk',
            ])
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', __('This job is already on your queue.'));
    }

    public function test_desk_claim_returns_json_success_payload(): void
    {
        $designer = $this->userWithRole('Designer');
        $companyId = $designer->company_id;
        $branchId = $designer->default_branch_id;
        session(['active_company_id' => $companyId, 'active_branch_id' => $branchId]);

        $customer = Customer::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $request = ArtworkRequest::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'customer_id' => $customer->id,
            'request_number' => 'AW-CLAIM-JSON',
            'title' => 'JSON Claim',
            'priority' => ArtworkPriority::Normal,
            'status' => ArtworkRequestStatus::Requested,
            'assigned_designer_id' => null,
            'requested_by' => $designer->id,
            'current_version' => 0,
        ]);

        $this->actingAs($designer)
            ->post(route('admin.artwork.claim', $request), [
                'from' => 'designer-desk',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['ok', 'message', 'redirect']);

        $request->refresh();
        $this->assertSame($designer->id, $request->assigned_designer_id);
    }

    protected function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName($role, 'web'));

        return $user;
    }
}
