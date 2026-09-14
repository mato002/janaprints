@php
    $hasCompanyScope = $rows->contains(fn ($row) => in_array('company', $row['scopes'], true));
    $hasBranchScope = $branchId && $rows->contains(fn ($row) => in_array('branch', $row['scopes'], true));
@endphp

<style>
    .settings-grid-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }
    .settings-grid-table th,
    .settings-grid-table td {
        white-space: normal !important;
        max-width: none !important;
        overflow: visible !important;
        text-overflow: clip !important;
        height: auto !important;
        vertical-align: top;
        word-break: break-word;
    }
    .settings-grid-table .erp-select,
    .settings-grid-table .erp-input {
        min-width: 0 !important;
        width: 100%;
        max-width: 100%;
    }
</style>

<div class="min-w-0 w-full max-w-full">
<table class="erp-table erp-table--grid settings-grid-table text-sm">
    <colgroup>
        <col style="width: {{ $hasBranchScope ? '44%' : '56%' }}">
        @if ($hasCompanyScope)
            <col style="width: {{ $hasBranchScope ? '18%' : '24%' }}">
        @endif
        @if ($hasBranchScope)
            <col style="width: 18%">
        @endif
        <col style="width: {{ $hasBranchScope ? '20%' : '20%' }}">
    </colgroup>
    <thead>
        <tr>
            <th scope="col">{{ __('Setting') }}</th>
            @if ($editable)
                @if ($hasCompanyScope)
                    <th scope="col">{{ __('Company') }}</th>
                @endif
                @if ($hasBranchScope)
                    <th scope="col">{{ __('Branch') }}</th>
                @endif
            @else
                <th scope="col">{{ __('Company') }}</th>
                @if ($branchId)
                    <th scope="col">{{ __('Branch') }}</th>
                @endif
            @endif
            <th scope="col">{{ __('Now') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            @php
                $companyValue = $row['company_value'] ?? $row['effective_value'];
            @endphp
            <tr>
                <td>
                    <p class="font-medium text-erp-primary">{{ $row['label'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $row['description'] }}</p>
                </td>

                @if ($editable)
                    @if ($hasCompanyScope)
                        <td>
                            @if (in_array('company', $row['scopes'], true))
                                @include('admin.settings.partials.setting-input', [
                                    'name' => "settings[{$row['key']}][company]",
                                    'type' => $row['type'],
                                    'value' => $companyValue,
                                    'placeholder' => __('Off / On'),
                                    'allowInherit' => false,
                                ])
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    @endif

                    @if ($hasBranchScope)
                        <td>
                            @if (in_array('branch', $row['scopes'], true))
                                @include('admin.settings.partials.setting-input', [
                                    'name' => "settings[{$row['key']}][branch]",
                                    'type' => $row['type'],
                                    'value' => $row['branch_value'],
                                    'placeholder' => __('Inherit'),
                                    'allowInherit' => true,
                                ])
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    @endif
                @else
                    <td>
                        @include('admin.settings.partials.setting-display', [
                            'value' => $row['company_value'],
                            'type' => $row['type'],
                            'empty' => __('Default'),
                        ])
                    </td>
                    @if ($branchId)
                        <td>
                            @include('admin.settings.partials.setting-display', [
                                'value' => $row['branch_value'],
                                'type' => $row['type'],
                                'empty' => __('Inherit'),
                            ])
                        </td>
                    @endif
                @endif

                <td>
                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800">
                        @include('admin.settings.partials.setting-display', [
                            'value' => $row['effective_value'],
                            'type' => $row['type'],
                            'empty' => '—',
                        ])
                    </span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
