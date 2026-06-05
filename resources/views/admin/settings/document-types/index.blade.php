@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'configuration']);
@endphp

<x-admin-layout
    :title="__('Document Types')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Configuration'), 'url' => $hubBackUrl],
        ['label' => __('Document Types')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Document Types'),
        'description' => __('Central registry for ERP document classification, numbering, approvals, and retention.'),
        'backUrl' => $hubBackUrl,
    ])

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.document-types.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ])

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <x-admin.card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                {{ __(':count document types registered', ['count' => count($rows)]) }}
            </p>
            @if ($canCreate)
                <a href="{{ route('admin.settings.document-types.create', $scopeQuery) }}" class="erp-btn erp-btn--primary">
                    {{ __('Create Document Type') }}
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3">{{ __('Document Type') }}</th>
                        <th class="py-3 px-2">{{ __('Module') }}</th>
                        <th class="py-3 px-2">{{ __('Prefix') }}</th>
                        <th class="py-3 px-2">{{ __('Number Series') }}</th>
                        <th class="py-3 px-2">{{ __('Approval') }}</th>
                        <th class="py-3 px-2">{{ __('Retention') }}</th>
                        <th class="py-3 px-2">{{ __('Status') }}</th>
                        <th class="py-3 pl-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-erp-border">
                            <td class="py-3 pr-3">
                                <div class="font-medium text-slate-900">{{ $row['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $row['code'] }}</div>
                            </td>
                            <td class="py-3 px-2">{{ $row['module'] }}</td>
                            <td class="py-3 px-2 font-mono text-xs">{{ $row['prefix'] ?: '—' }}</td>
                            <td class="py-3 px-2 text-xs">{{ $row['number_series'] }}</td>
                            <td class="py-3 px-2 text-xs">
                                @if ($row['approval_required'])
                                    <span class="text-amber-700">{{ $row['approval_rule'] }}</span>
                                @else
                                    <span class="text-slate-500">{{ __('Not required') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-xs">{{ $row['retention_period'] }}</td>
                            <td class="py-3 px-2">
                                @if ($row['is_active'])
                                    <span class="erp-badge erp-badge--success">{{ $row['status'] }}</span>
                                @else
                                    <span class="erp-badge erp-badge--muted">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 pl-2 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($canEdit)
                                        <a href="{{ route('admin.settings.document-types.edit', ['documentTypeDefinition' => $row['id']] + $scopeQuery) }}" class="erp-btn erp-btn--ghost erp-btn--sm">
                                            {{ __('Edit') }}
                                        </a>
                                    @endif
                                    @if ($row['is_active'] && $canDeactivate)
                                        <form method="POST" action="{{ route('admin.settings.document-types.deactivate', ['documentTypeDefinition' => $row['id']] + $scopeQuery) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-red-600">
                                                {{ __('Deactivate') }}
                                            </button>
                                        </form>
                                    @elseif (! $row['is_active'] && $canActivate)
                                        <form method="POST" action="{{ route('admin.settings.document-types.activate', ['documentTypeDefinition' => $row['id']] + $scopeQuery) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-emerald-700">
                                                {{ __('Activate') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                {{ __('No document types configured for this scope.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
