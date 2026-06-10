<?php

namespace Tests\Feature\PrintingIntelligence;

class PrintingIntelligenceOverviewWorkspaceTest extends MachineIntelligenceWorkspaceTest
{
    public function test_overview_shows_executive_tiles(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.overview'))
            ->assertOk()
            ->assertSee(__('Artwork Analyses'))
            ->assertSee(__('Quotation Estimates'))
            ->assertSee(__('Quick links'));
    }
}
