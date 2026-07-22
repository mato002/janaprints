<?php

namespace App\Http\Controllers\Admin\Artwork;

use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkRequest;
use App\Support\Artwork\DesignerDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignerDeskController extends Controller
{
    public function index(Request $request, DesignerDeskService $desk): View
    {
        $this->authorize('viewAny', ArtworkRequest::class);

        return view('admin.artwork.desk.index', $desk->build($request));
    }

    public function panel(Request $request, ArtworkRequest $artworkRequest, DesignerDeskService $desk): JsonResponse
    {
        $this->authorize('view', $artworkRequest);

        $operatorMode = $request->user()?->prefersDesignerOperatorMode() ?? false;

        return response()->json($desk->panel($artworkRequest, $request->user(), $operatorMode));
    }
}
