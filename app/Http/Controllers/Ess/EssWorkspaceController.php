<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ess\Concerns\ResolvesEmployee;
use App\Support\Ess\EssWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EssWorkspaceController extends Controller
{
    use ResolvesEmployee;

    public function __invoke(Request $request, EssWorkspaceService $workspace): View
    {
        $user = $this->essUser();
        $employee = $this->essEmployee();
        $data = $workspace->build($employee, $user);

        $tab = (string) $request->query('tab', 'overview');
        $validTabs = collect($data['tabs'])->pluck('id');

        abort_unless($validTabs->contains($tab), 404);

        if ($request->filled('period') && $tab === 'payslips') {
            $period = (string) $request->query('period');
            $data['payslips'] = $data['payslips']->filter(function ($payslip) use ($period) {
                $run = $payslip->payrollRun;

                return $run && $run->period_start?->format('Y-m') === $period;
            })->values();
        }

        return view('ess.workspace', [
            ...$data,
            'activeTab' => $tab,
        ]);
    }
}
