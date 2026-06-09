<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\Hr\Employee360WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Employee360Controller extends Controller
{
    public function __construct(
        protected Employee360WorkspaceService $workspace,
    ) {}

    public function show(Request $request, Employee $employee): View
    {
        $this->authorize('view', $employee);

        return view('admin.hr.employees.show', $this->workspace->build($employee));
    }
}
