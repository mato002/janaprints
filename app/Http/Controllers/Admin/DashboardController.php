<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Operator\OperatorModeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($homeUrl = OperatorModeRegistry::resolveHomeUrl($request->user())) {
            return redirect()->to($homeUrl);
        }

        return view('admin.dashboard');
    }
}
