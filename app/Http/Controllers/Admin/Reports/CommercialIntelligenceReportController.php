<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Support\Commercial\Intelligence\CommercialIntelligencePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialIntelligenceReportController extends Controller
{
    public function index(Request $request, CommercialIntelligencePresenter $presenter): View
    {
        abort_unless(
            $request->user()?->can('intelligence.commercial.view') || $request->user()?->can('reports.view'),
            403,
        );

        return view('admin.reports.commercial-intelligence.index', $presenter->present($request));
    }
}
