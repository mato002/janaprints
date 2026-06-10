<?php

namespace Tests\Feature\PrintingIntelligence;

class CostIntelligenceWorkspaceTest extends MachineIntelligenceWorkspaceTest
{
    public function test_workspace_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.cost'))
            ->assertOk()
            ->assertSee(__('Cost Intelligence'))
            ->assertSee(__('Average Job Cost'));
    }
}
