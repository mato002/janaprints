<?php

namespace Tests\Feature\PrintingIntelligence;

class QuotationIntelligenceWorkspaceTest extends MachineIntelligenceWorkspaceTest
{
    public function test_workspace_loads(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.quotations'))
            ->assertOk()
            ->assertSee(__('Quotation Intelligence'))
            ->assertSee(__('Average Recommended Margin'));
    }
}
