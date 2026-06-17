<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\Hr\EmployeeEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeEmailController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected EmployeeEmailService $email,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('email', Employee::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $allActive = $request->boolean('all');
        $employeeIds = $request->input('employees', []);

        if (! is_array($employeeIds)) {
            $employeeIds = filled($employeeIds) ? [(string) $employeeIds] : [];
        }

        $recipients = $this->email->resolveRecipients($companyId, $employeeIds, $allActive);

        if ($recipients->isEmpty()) {
            return redirect()
                ->route('admin.employees.index')
                ->with('status', __('No employees with email addresses were found for this selection.'));
        }

        foreach ($recipients as $employee) {
            $this->authorize('view', $employee);
        }

        return view('admin.employees.email.compose', [
            'recipients' => $recipients,
            'allActive' => $allActive,
            'subject' => old('subject'),
            'body' => old('body'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('email', Employee::class);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'employees' => ['nullable', 'array'],
            'employees.*' => ['integer', 'exists:employees,id'],
            'all' => ['nullable', 'boolean'],
        ]);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $allActive = $request->boolean('all');
        $employeeIds = $validated['employees'] ?? [];

        $recipients = $this->email->resolveRecipients($companyId, $employeeIds, $allActive);

        foreach ($recipients as $employee) {
            $this->authorize('view', $employee);
        }

        $result = $this->email->sendMessage(
            $companyId,
            (int) $request->user()->id,
            $recipients,
            $validated['subject'],
            $validated['body'],
        );

        $message = $result['queued'] === 1
            ? __('Email queued for :count employee.', ['count' => $result['queued']])
            : __('Emails queued for :count employees.', ['count' => $result['queued']]);

        if ($result['skipped'] > 0) {
            $message .= ' '.($result['skipped'] === 1
                ? __(':count recipient skipped.', ['count' => $result['skipped']])
                : __(':count recipients skipped.', ['count' => $result['skipped']]));
        }

        return redirect()
            ->route('admin.employees.index')
            ->with('status', $message);
    }
}
