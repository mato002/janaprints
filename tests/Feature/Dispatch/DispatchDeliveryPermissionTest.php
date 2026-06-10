<?php

namespace Tests\Feature\Dispatch;

use App\Models\User;
use App\Services\Dispatch\DeliveryNoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DispatchDeliveryPermissionTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_unauthorized_user_cannot_dispatch(): void
    {
        [$note, , , $jobCard] = $this->prepareDraftNoteWithFg();

        $viewer = User::factory()->create([
            'company_id' => $jobCard->company_id,
            'default_branch_id' => $jobCard->branch_id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Dispatch Viewer '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.view']);
        $viewer->assignRole($role);

        session(['active_company_id' => $jobCard->company_id, 'active_branch_id' => $jobCard->branch_id]);

        $this->actingAs($viewer)
            ->post(route('admin.dispatch.delivery-notes.dispatch', $note))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_deliver(): void
    {
        [$note, , , $jobCard] = $this->readyDispatchedDeliveryNote();

        $viewer = User::factory()->create([
            'company_id' => $jobCard->company_id,
            'default_branch_id' => $jobCard->branch_id,
            'email_verified_at' => now(),
        ]);
        $role = Role::create(['name' => 'Dispatch Viewer 2 '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['dispatch.view']);
        $viewer->assignRole($role);

        session(['active_company_id' => $jobCard->company_id, 'active_branch_id' => $jobCard->branch_id]);

        $this->actingAs($viewer)
            ->post(route('admin.dispatch.delivery-notes.deliver', $note))
            ->assertForbidden();
    }
}
