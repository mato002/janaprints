<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Models\PrintingIntelligence\PrintInkProfile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

class InkIntelligenceWorkspaceTest extends MachineIntelligenceWorkspaceTest
{
    public function test_workspace_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee(__('Ink Intelligence'))
            ->assertSee(__('Highest Cost Ink'));
    }

    public function test_manage_ink_profiles_link_appears_with_permission(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.ink-profiles.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee(__('Manage Ink Profiles'));
    }

    public function test_warning_appears_when_no_active_profiles(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.ink-profiles.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee(__('Ink estimation requires at least one active ink profile.'));
    }

    public function test_profile_appears_in_ink_intelligence_after_creation(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view', 'printing.ink-profiles.view']);

        PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'Workspace CMYK',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 4500,
            'estimated_ml' => 900,
            'cost_per_ml' => 5,
            'active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.ink'))
            ->assertOk()
            ->assertSee('Workspace CMYK')
            ->assertDontSee(__('Ink estimation requires at least one active ink profile.'));
    }
}
