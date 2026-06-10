<?php

namespace Tests\Feature\PrintingIntelligence;

class MaterialIntelligenceWorkspaceTest extends MachineIntelligenceWorkspaceTest
{
    public function test_workspace_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.material'))
            ->assertOk()
            ->assertSee(__('Material Intelligence'))
            ->assertSee(__('Dead Stock Value'));
    }
}
