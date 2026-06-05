<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Support\Assets\Asset360Presenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Asset360Controller extends Controller
{
    public function __construct(
        protected Asset360Presenter $presenter,
    ) {}

    public function show(Request $request, FixedAsset $asset): View
    {
        $this->authorize('view360', $asset);

        $workspace = $this->presenter->present($asset, $request->query('tab'));

        return view('admin.assets.360.show', $workspace);
    }
}
