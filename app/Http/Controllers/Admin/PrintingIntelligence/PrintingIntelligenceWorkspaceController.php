<?php

namespace App\Http\Controllers\Admin\PrintingIntelligence;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class PrintingIntelligenceWorkspaceController extends Controller
{
    public function hub(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.intelligence.view'), 403);

        return redirect()->route('admin.printing-intelligence.overview');
    }
}
