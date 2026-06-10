<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->scopeToTenant(
            User::query()->with(['company', 'defaultBranch', 'roles'])
        )->latest()->paginate(15);

        $lastLogins = ActivityLog::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->where('action', 'login')
            ->selectRaw('user_id, MAX(created_at) as last_login_at')
            ->groupBy('user_id')
            ->pluck('last_login_at', 'user_id');

        return view('admin.users.index', compact('users', 'lastLogins'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', User::class);

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            abort(404);
        }

        $users = $this->scopeToTenant(
            User::query()->with(['company', 'defaultBranch', 'roles'])
        )->latest()->get();

        $lastLogins = ActivityLog::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->where('action', 'login')
            ->selectRaw('user_id, MAX(created_at) as last_login_at')
            ->groupBy('user_id')
            ->pluck('last_login_at', 'user_id');

        $headers = [__('Name'), __('Email'), __('Role'), __('Branch'), __('Status'), __('Last login')];
        $rows = $users->map(function (User $user) use ($lastLogins) {
            $lastLogin = $lastLogins[$user->id] ?? null;

            return [
                $user->name,
                $user->email,
                $user->getRoleNames()->first() ?? '',
                $user->defaultBranch?->name ?? '',
                $user->is_active ? __('Active') : __('Inactive'),
                $lastLogin ? Carbon::parse($lastLogin)->format('Y-m-d H:i') : '',
            ];
        })->all();

        return $writer->download(
            $format,
            'users-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            __('Users'),
        );
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->validateUser($request);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        $payload = collect($data)->except('role')->toArray();
        $payload['is_active'] = $request->boolean('is_active');
        $user = User::query()->create($payload);
        $user->syncRoles([$data['role']]);

        ActivityLogger::log('created', $user);

        return redirect()->route('admin.users.index')->with('status', __('User created.'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', array_merge(['user' => $user], $this->formData($user)));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validateUser($request, $user);
        $beforeRole = $user->getRoleNames()->first();
        $beforeBranch = $user->default_branch_id;
        $beforeActive = $user->is_active;
        $payload = collect($data)->except(['role', 'password'])->toArray();
        $payload['is_active'] = $request->boolean('is_active');
        $user->update($payload);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        $afterRole = $user->getRoleNames()->first();
        $afterBranch = $user->default_branch_id;

        ActivityLogger::log('updated', $user, null, [
            'role' => $afterRole,
            'default_branch_id' => $afterBranch,
            'is_active' => $user->is_active,
        ], [
            'role' => $beforeRole,
            'default_branch_id' => $beforeBranch,
            'is_active' => $beforeActive,
        ]);

        if ($beforeRole !== $afterRole) {
            ActivityLogger::log('role_assignment', $user, null, ['role' => $afterRole], ['role' => $beforeRole]);
        }

        if ((int) $beforeBranch !== (int) $afterBranch) {
            ActivityLogger::log('branch_assignment', $user, null, ['default_branch_id' => $afterBranch], ['default_branch_id' => $beforeBranch]);
        }

        return redirect()->route('admin.users.index')->with('status', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        ActivityLogger::log('deleted', $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('User deleted.'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);
        ActivityLogger::log('password_reset', $user);

        return back()->with('status', __('Password reset successfully.'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $user->update(['is_active' => ! $user->is_active]);
        ActivityLogger::log($user->is_active ? 'activated' : 'deactivated', $user);

        return back()->with('status', __('User status updated.'));
    }

    protected function validateUser(Request $request, ?User $user = null): array
    {
        $companyId = $this->resolveCompanyId($request);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Rules\Password::defaults()],
            'company_id' => ['required', 'exists:companies,id'],
            'default_branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'is_active' => ['boolean'],
        ]);
    }

    protected function resolveCompanyId(Request $request): int
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return (int) $request->input('company_id');
        }

        return (int) auth()->user()->company_id;
    }

    protected function formData(?User $user = null): array
    {
        $companies = auth()->user()->hasRole('Super Admin')
            ? Company::query()->where('is_active', true)->orderBy('name')->get()
            : Company::query()->where('id', auth()->user()->company_id)->get();

        $companyId = $user?->company_id ?? tenant()->companyId() ?? $companies->first()?->id;

        return [
            'companies' => $companies,
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->get(),
            'roles' => Role::query()->where('guard_name', 'web')->where('name', '!=', 'Super Admin')->orderBy('name')->get(),
        ];
    }
}
