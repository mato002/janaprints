<x-admin-layout :title="__('Documents')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Documents'), 'url' => route('admin.hr.documents.dashboard')], ['label' => __('Repository')]]">
    <x-admin.page-header :title="__('Document Repository')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.documents.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeDocument::class)
                <a href="{{ route('admin.hr.documents.create') }}" class="erp-btn-primary">{{ __('Upload document') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <form method="GET" class="erp-card mb-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="erp-label">{{ __('Employee') }}</label>
                <select name="employee_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Category') }}</label>
                <select name="category" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['categories'] as $category)
                        <option value="{{ $category->value }}" @selected(($filters['category'] ?? '') === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Expiry') }}</label>
                <select name="expiry" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="expiring" @selected(($filters['expiry'] ?? '') === 'expiring')>{{ __('Expiring soon') }}</option>
                    <option value="expired" @selected(($filters['expiry'] ?? '') === 'expired')>{{ __('Expired') }}</option>
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Search') }}</label>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="erp-input w-full text-sm" placeholder="{{ __('Title or employee…') }}">
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Filter') }}</button>
            <a href="{{ route('admin.hr.documents.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
        </div>
    </form>

    <x-admin.data-table :search-placeholder="__('Search documents…')" export-filename="employee-documents">
        <x-slot name="head">
            <tr>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Version') }}</th>
                <th>{{ __('Expires') }}</th>
                <th>{{ __('Updated') }}</th>
                <th></th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($documents as $document)
                <tr>
                    <td>{{ $document->employee->full_name }}</td>
                    <td>
                        <a href="{{ route('admin.hr.documents.show', $document) }}" class="font-medium text-indigo-600 hover:underline">{{ $document->title }}</a>
                    </td>
                    <td>{{ $document->category->label() }}</td>
                    <td>v{{ $document->current_version }}</td>
                    <td>
                        @if ($document->expires_at)
                            <span @class([
                                'text-rose-600' => $document->isExpired(),
                                'text-amber-600' => $document->isExpiringSoon(),
                            ])>{{ $document->expires_at->format('Y-m-d') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $document->updated_at->format('Y-m-d') }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.hr.documents.download', $document) }}" class="erp-btn-secondary text-xs">{{ __('Download') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-slate-500">{{ __('No documents found.') }}</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $documents->links() }}</div>
</x-admin-layout>
