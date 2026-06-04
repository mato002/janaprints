@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
@endphp

<x-admin-layout
    :title="__('Numbering')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Settings'), 'url' => $hubBackUrl],
        ['label' => __('Numbering')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Document Numbering'),
        'description' => __('Configure prefixes, padding, and next numbers for each document type.'),
        'backUrl' => $hubBackUrl,
    ])

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.numbering.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ])

    <x-admin.card>
        @if ($canManage)
            <form method="POST" action="{{ route('admin.settings.numbering.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="company_id" value="{{ $companyId }}">
                @if ($branchId)
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @endif
        @endif

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3">{{ __('Document') }}</th>
                        <th class="py-3 px-2">{{ __('Prefix') }}</th>
                        <th class="py-3 px-2 text-center">{{ __('Branch') }}</th>
                        <th class="py-3 px-2 text-center">{{ __('Year') }}</th>
                        <th class="py-3 px-2 text-center">{{ __('Month') }}</th>
                        <th class="py-3 px-2">{{ __('Padding') }}</th>
                        <th class="py-3 px-2">{{ __('Next #') }}</th>
                        <th class="py-3 px-2 text-center">{{ __('Active') }}</th>
                        <th class="py-3 pl-2">{{ __('Preview') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-erp-border">
                    @foreach ($rows as $row)
                        <tr>
                            <td class="py-3 pr-3 align-top">
                                <p class="font-medium text-erp-primary">{{ $row['label'] }}</p>
                                <p class="font-mono text-[11px] text-slate-400">{{ $row['type_code'] }}</p>
                            </td>
                            <td class="py-3 px-2 align-top">
                                @if ($canManage)
                                    <input
                                        type="text"
                                        name="sequences[{{ $row['document_type'] }}][prefix]"
                                        value="{{ $row['prefix'] }}"
                                        class="erp-input w-24"
                                        maxlength="20"
                                    >
                                @else
                                    {{ $row['prefix'] }}
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                @if ($canManage)
                                    <input type="hidden" name="sequences[{{ $row['document_type'] }}][include_branch]" value="0">
                                    <input type="checkbox" name="sequences[{{ $row['document_type'] }}][include_branch]" value="1" class="rounded border-erp-border text-erp-accent" @checked($row['include_branch'])>
                                @else
                                    {{ $row['include_branch'] ? __('Yes') : __('No') }}
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                @if ($canManage)
                                    <input type="hidden" name="sequences[{{ $row['document_type'] }}][include_year]" value="0">
                                    <input type="checkbox" name="sequences[{{ $row['document_type'] }}][include_year]" value="1" class="rounded border-erp-border text-erp-accent" @checked($row['include_year'])>
                                @else
                                    {{ $row['include_year'] ? __('Yes') : __('No') }}
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                @if ($canManage)
                                    <input type="hidden" name="sequences[{{ $row['document_type'] }}][include_month]" value="0">
                                    <input type="checkbox" name="sequences[{{ $row['document_type'] }}][include_month]" value="1" class="rounded border-erp-border text-erp-accent" @checked($row['include_month'])>
                                @else
                                    {{ $row['include_month'] ? __('Yes') : __('No') }}
                                @endif
                            </td>
                            <td class="py-3 px-2 align-top">
                                @if ($canManage)
                                    <input type="number" name="sequences[{{ $row['document_type'] }}][padding]" value="{{ $row['padding'] }}" min="1" max="10" class="erp-input w-16">
                                @else
                                    {{ $row['padding'] }}
                                @endif
                            </td>
                            <td class="py-3 px-2 align-top">
                                @if ($canManage)
                                    <input type="number" name="sequences[{{ $row['document_type'] }}][next_number]" value="{{ $row['next_number'] }}" min="1" class="erp-input w-24">
                                @else
                                    {{ $row['next_number'] }}
                                @endif
                            </td>
                            <td class="py-3 px-2 text-center align-top">
                                @if ($canManage)
                                    <input type="hidden" name="sequences[{{ $row['document_type'] }}][active]" value="0">
                                    <input type="checkbox" name="sequences[{{ $row['document_type'] }}][active]" value="1" class="rounded border-erp-border text-erp-accent" @checked($row['active'])>
                                @else
                                    <x-admin.status-badge :variant="$row['active'] ? 'success' : 'danger'">
                                        {{ $row['active'] ? __('Yes') : __('No') }}
                                    </x-admin.status-badge>
                                @endif
                            </td>
                            <td class="py-3 pl-2 align-top">
                                <code class="text-xs text-slate-600">{{ $row['preview'] }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($canManage)
                <div class="mt-6 border-t border-erp-border pt-6">
                    <x-primary-button>{{ __('Save numbering') }}</x-primary-button>
                </div>
            </form>
        @endif
    </x-admin.card>
</x-admin-layout>
