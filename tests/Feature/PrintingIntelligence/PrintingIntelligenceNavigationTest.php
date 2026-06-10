<?php

namespace Tests\Feature\PrintingIntelligence;

use Illuminate\Support\Facades\Route;

class PrintingIntelligenceNavigationTest extends MachineIntelligenceWorkspaceTest
{
    public function test_all_workspace_routes_registered(): void
    {
        $routes = [
            'admin.printing-intelligence.overview',
            'admin.printing-intelligence.machines',
            'admin.printing-intelligence.ink',
            'admin.printing-intelligence.inks',
            'admin.printing-intelligence.material',
            'admin.printing-intelligence.materials',
            'admin.printing-intelligence.cost',
            'admin.printing-intelligence.cost-intelligence',
            'admin.printing-intelligence.quotations',
            'admin.printing-intelligence.quotation-intelligence',
            'admin.printing-intelligence.estimate-vs-actual',
            'admin.printing-intelligence.calibration-governance',
            'admin.printing-intelligence.production-profitability',
            'admin.printing-intelligence.executive-intelligence',
        ];

        foreach ($routes as $route) {
            $this->assertTrue(Route::has($route), "Missing route: {$route}");
        }
    }

    public function test_hub_hides_unauthorized_sections(): void
    {
        [$company, $branch, $user] = $this->userWith(['printing.intelligence.view']);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.overview'))
            ->assertOk()
            ->assertDontSee(__('Executive Intelligence'))
            ->assertDontSee(__('Production Profitability'));
    }
}
