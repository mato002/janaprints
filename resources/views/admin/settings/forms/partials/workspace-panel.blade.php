@props(['form', 'canManage'])

<x-admin.card class="!p-0 overflow-hidden" id="form-panel-{{ $form['form_key'] }}">
    <div class="border-b border-erp-border px-5 py-4 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                    {{ __('Forms') }}
                    <span class="mx-1 text-slate-300">/</span>
                    <span class="text-erp-accent">{{ $form['label'] }}</span>
                </p>
                <h2 class="mt-1 text-lg font-semibold text-erp-primary">{{ $form['label'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $form['description'] }}</p>
                @if ($form['inherits_company'])
                    <p class="mt-2 text-xs text-amber-700">{{ __('No branch override — company default applies.') }}</p>
                @endif
            </div>
            @if ($canManage)
                <label class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-erp-border bg-erp-page/60 px-3 py-2 text-sm">
                    <input type="hidden" name="forms[{{ $form['form_key'] }}][is_active]" value="0">
                    <input
                        type="checkbox"
                        name="forms[{{ $form['form_key'] }}][is_active]"
                        value="1"
                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        @checked($form['is_active'])
                    >
                    <span class="font-medium text-slate-700">{{ __('Form active') }}</span>
                </label>
            @else
                <x-admin.status-badge :variant="$form['is_active'] ? 'success' : 'danger'">
                    {{ $form['is_active'] ? __('Active') : __('Inactive') }}
                </x-admin.status-badge>
            @endif
        </div>
    </div>

    @if ($canManage)
        <div class="border-b border-erp-border bg-erp-page/50 px-5 py-4 sm:px-6">
            <h3 class="text-sm font-semibold text-erp-primary">{{ __('Add custom field') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('Use lowercase keys with underscores (e.g. tax_id). Custom fields are saved for this company/branch scope.') }}</p>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-input-label for="add_field_key" :value="__('Field key')" />
                    <input
                        type="text"
                        id="add_field_key"
                        name="forms[{{ $form['form_key'] }}][add_field][field_key]"
                        class="erp-input mt-1 w-full font-mono text-sm"
                        placeholder="custom_field"
                        pattern="[a-z][a-z0-9_]*"
                    >
                </div>
                <div>
                    <x-input-label for="add_field_label" :value="__('Label')" />
                    <input
                        type="text"
                        id="add_field_label"
                        name="forms[{{ $form['form_key'] }}][add_field][label]"
                        class="erp-input mt-1 w-full"
                        placeholder="{{ __('Display label') }}"
                    >
                </div>
                <div>
                    <x-input-label for="add_field_type" :value="__('Type')" />
                    <select id="add_field_type" name="forms[{{ $form['form_key'] }}][add_field][type]" class="erp-select mt-1 w-full">
                        <option value="text">{{ __('Text') }}</option>
                        <option value="email">{{ __('Email') }}</option>
                        <option value="number">{{ __('Number') }}</option>
                        <option value="date">{{ __('Date') }}</option>
                        <option value="textarea">{{ __('Textarea') }}</option>
                        <option value="select">{{ __('Select') }}</option>
                        <option value="checkbox">{{ __('Checkbox') }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <p class="text-xs text-slate-500">{{ __('Click Save below to create the field, then configure visibility and rules in the table.') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="erp-table erp-table--grid">
            <thead>
                <tr>
                    <th class="pl-5 sm:pl-6">{{ __('Field') }}</th>
                    <th>{{ __('Visibility') }}</th>
                    <th>{{ __('Requirement') }}</th>
                    <th>{{ __('Read only') }}</th>
                    <th>{{ __('Default value') }}</th>
                    @if ($canManage)
                        <th class="pr-5 sm:pr-6 text-right">{{ __('Actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border bg-white">
                @foreach ($form['fields'] as $field)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 pl-5 font-medium text-slate-700 sm:pl-6">
                            <div class="flex items-center gap-2">
                                <span>{{ __($field['label']) }}</span>
                                @if (! empty($field['is_custom']))
                                    <span class="rounded bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-violet-700">{{ __('Custom') }}</span>
                                @else
                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ __('System') }}</span>
                                @endif
                            </div>
                            <span class="block font-mono text-xs font-normal text-slate-400">{{ $field['field_key'] }}</span>
                        </td>
                        <td class="py-3">
                            @if ($canManage)
                                <select name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][visibility]" class="erp-select w-full min-w-[7rem] max-w-[9rem]">
                                    <option value="visible" @selected($field['visible'])>{{ __('Visible') }}</option>
                                    <option value="hidden" @selected($field['hidden'])>{{ __('Hidden') }}</option>
                                </select>
                            @else
                                <span class="text-slate-600">{{ $field['hidden'] ? __('Hidden') : __('Visible') }}</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if ($canManage)
                                <select name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][requirement]" class="erp-select w-full min-w-[7rem] max-w-[9rem]">
                                    <option value="required" @selected($field['required'])>{{ __('Required') }}</option>
                                    <option value="optional" @selected(! $field['required'])>{{ __('Optional') }}</option>
                                </select>
                            @else
                                <span class="text-slate-600">{{ $field['required'] ? __('Required') : __('Optional') }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if ($canManage)
                                <input type="hidden" name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][read_only]" value="0">
                                <input
                                    type="checkbox"
                                    name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][read_only]"
                                    value="1"
                                    class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                    @checked($field['read_only'])
                                >
                            @else
                                <span class="text-slate-600">{{ $field['read_only'] ? __('Yes') : __('No') }}</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if ($canManage)
                                <input
                                    type="text"
                                    name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][default_value]"
                                    value="{{ $field['default'] ?? '' }}"
                                    class="erp-input w-full min-w-[8rem] max-w-xs"
                                    placeholder="—"
                                >
                            @else
                                <span class="text-slate-600">{{ $field['default'] ?? '—' }}</span>
                            @endif
                        </td>
                        @if ($canManage)
                            <td class="py-3 pr-5 text-right sm:pr-6">
                                @if (! empty($field['is_custom']))
                                    <label class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                                        <input
                                            type="checkbox"
                                            name="forms[{{ $form['form_key'] }}][remove_fields][]"
                                            value="{{ $field['field_key'] }}"
                                            class="rounded border-red-300 text-red-600 focus:ring-red-500"
                                        >
                                        {{ __('Remove') }}
                                    </label>
                                @else
                                    <span class="text-xs text-slate-400" title="{{ __('System fields cannot be removed') }}">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
