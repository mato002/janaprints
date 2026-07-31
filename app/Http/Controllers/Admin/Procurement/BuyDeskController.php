<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Vendor;
use App\Support\Procurement\BuyDeskPageBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyDeskController extends Controller
{
    public function __construct(
        protected BuyDeskPageBuilder $pageBuilder,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vendor::class);

        return view('admin.procurement.desk.index', $this->pageBuilder->build($request));
    }
}
