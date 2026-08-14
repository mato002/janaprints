<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\BuyDeskPageBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyDeskController extends Controller
{
    public function __construct(
        protected BuyDeskPageBuilder $pageBuilder,
    ) {}

    public function index(Request $request): View
    {
        if (! $request->user()?->can('viewAny', Vendor::class)
            && ! $request->user()?->can('viewAny', PurchaseRequest::class)) {
            throw new AuthorizationException;
        }

        return view('admin.procurement.desk.index', $this->pageBuilder->build($request));
    }
}
