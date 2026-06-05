<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\EmployeeDocumentCategory;
use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeDocumentVersion;
use App\Support\Hr\EmployeeDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDocumentController extends Controller
{
    public function __construct(
        protected EmployeeDocumentService $documents,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.documents.index', [
            'documents' => $this->documents->paginate($companyId, $request->only([
                'employee_id', 'category', 'expiry', 'search',
            ])),
            'filters' => $request->only(['employee_id', 'category', 'expiry', 'search']),
            'formData' => $this->documents->formData($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeDocument::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.documents.create', [
            'formData' => $this->documents->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeDocument::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'category' => ['required', Rule::enum(EmployeeDocumentCategory::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'expires_at' => ['nullable', 'date'],
            'renewal_reminder_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $document = $this->documents->create(
            $companyId,
            $validated,
            $request->file('file'),
            $request->user(),
        );

        return redirect()
            ->route('admin.hr.documents.show', $document)
            ->with('status', __('Document uploaded.'));
    }

    public function show(EmployeeDocument $employeeDocument): View
    {
        $this->authorize('view', $employeeDocument);

        $employeeDocument->load(['employee', 'versions.uploadedBy']);

        return view('admin.hr.documents.show', [
            'document' => $employeeDocument,
        ]);
    }

    public function uploadVersion(Request $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->authorize('upload', $employeeDocument);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->documents->uploadVersion(
            $employeeDocument,
            $request->file('file'),
            $request->user(),
            $validated['notes'] ?? null,
        );

        return back()->with('status', __('New version uploaded.'));
    }

    public function download(EmployeeDocument $employeeDocument)
    {
        $this->authorize('view', $employeeDocument);

        return $this->documents->download($employeeDocument);
    }

    public function downloadVersion(EmployeeDocument $employeeDocument, EmployeeDocumentVersion $employeeDocumentVersion)
    {
        $this->authorize('view', $employeeDocument);
        abort_unless($employeeDocumentVersion->employee_document_id === $employeeDocument->id, 404);

        return $this->documents->download($employeeDocument, $employeeDocumentVersion);
    }

    public function destroy(EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->authorize('delete', $employeeDocument);

        $this->documents->delete($employeeDocument);

        return redirect()
            ->route('admin.hr.documents.index')
            ->with('status', __('Document deleted.'));
    }
}
