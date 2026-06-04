<div class="erp-table-scroll overflow-x-auto">
    <table class="erp-table erp-table--grid min-w-full text-sm">
        <thead>
            <tr>
                <th scope="col">{{ __('Setting') }}</th>
                @if ($editable)
                    @if ($rows->contains(fn ($row) => in_array('company', $row['scopes'], true)))
                        <th scope="col" class="py-3 px-4">{{ __('Company override') }}</th>
                    @endif
                    @if ($branchId && $rows->contains(fn ($row) => in_array('branch', $row['scopes'], true)))
                        <th scope="col" class="py-3 px-4">{{ __('Branch override') }}</th>
                    @endif
                @else
                    <th scope="col" class="py-3 px-4">{{ __('Company value') }}</th>
                    @if ($branchId)
                        <th scope="col" class="py-3 px-4">{{ __('Branch value') }}</th>
                    @endif
                @endif
                <th scope="col">{{ __('Effective') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="py-4 pr-4 align-top">
                        <p class="font-medium text-erp-primary">{{ $row['label'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $row['description'] }}</p>
                        <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $row['key'] }}</p>
                    </td>

                    @if ($editable)
                        @if ($rows->contains(fn ($r) => in_array('company', $r['scopes'], true)))
                            <td class="py-4 px-4 align-top">
                                @if (in_array('company', $row['scopes'], true))
                                    @include('admin.settings.partials.setting-input', [
                                        'name' => "settings[{$row['key']}][company]",
                                        'type' => $row['type'],
                                        'value' => $row['company_value'],
                                        'placeholder' => __('Inherit default'),
                                        'allowInherit' => true,
                                    ])
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        @endif

                        @if ($branchId && $rows->contains(fn ($r) => in_array('branch', $r['scopes'], true)))
                            <td class="py-4 px-4 align-top">
                                @if (in_array('branch', $row['scopes'], true))
                                    @include('admin.settings.partials.setting-input', [
                                        'name' => "settings[{$row['key']}][branch]",
                                        'type' => $row['type'],
                                        'value' => $row['branch_value'],
                                        'placeholder' => __('Inherit company'),
                                        'allowInherit' => true,
                                    ])
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        @endif
                    @else
                        <td class="py-4 px-4 align-top tabular-nums text-slate-700">
                            @include('admin.settings.partials.setting-display', ['value' => $row['company_value'], 'type' => $row['type'], 'empty' => __('Default')])
                        </td>
                        @if ($branchId)
                            <td class="py-4 px-4 align-top tabular-nums text-slate-700">
                                @include('admin.settings.partials.setting-display', ['value' => $row['branch_value'], 'type' => $row['type'], 'empty' => __('Inherit')])
                            </td>
                        @endif
                    @endif

                    <td class="py-4 pl-4 align-top">
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800">
                            @include('admin.settings.partials.setting-display', ['value' => $row['effective_value'], 'type' => $row['type'], 'empty' => '—'])
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
