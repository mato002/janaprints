<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    public function index(): View
    {
        abort_unless(
            auth()->user()->can('viewAny', \App\Models\User::class)
                || auth()->user()->can('viewAny', \Spatie\Permission\Models\Role::class),
            403,
        );

        return view('admin.access-control.index', [
            'canViewUsers' => auth()->user()->can('viewAny', \App\Models\User::class),
            'canViewRoles' => auth()->user()->can('viewAny', \Spatie\Permission\Models\Role::class),
        ]);
    }
}
