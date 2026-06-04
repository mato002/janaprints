<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\PostingTemplate;
use Illuminate\View\View;

class PostingTemplateController extends Controller
{
    use ResolvesAccountingTenant;

    public function index(): View
    {
        $this->authorize('viewAny', PostingTemplate::class);

        $templates = PostingTemplate::query()
            ->forTenant()
            ->withCount('lines')
            ->orderBy('module')
            ->orderBy('code')
            ->paginate(30);

        return view('admin.accounting.posting.templates.index', compact('templates'));
    }

    public function show(PostingTemplate $template): View
    {
        $this->authorize('view', $template);

        $template->load(['lines.glAccount']);

        return view('admin.accounting.posting.templates.show', compact('template'));
    }
}
