@props(['form', 'canManage', 'backUrl' => null])

<x-admin.card class="!p-0 overflow-hidden" id="form-panel-{{ $form['form_key'] }}">
    <div class="border-b border-erp-border bg-erp-page/30 px-4 py-2 sm:px-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                @if ($backUrl)
                    <a
                        href="{{ $backUrl }}"
                        data-turbo-action="advance"
                        class="inline-flex shrink-0 items-center gap-1 font-medium text-slate-500 transition-colors hover:text-erp-accent"
                    >
                        <x-admin.icon name="chevron-left" class="h-3.5 w-3.5" />
                        {{ __('All forms') }}
                    </a>
                    <span class="text-slate-300" aria-hidden="true">/</span>
                @endif
                <span class="font-semibold text-erp-primary">{{ $form['label'] }}</span>
                @if ($form['inherits_company'])
                    <span class="text-xs text-amber-700">{{ __('Company default') }}</span>
                @endif
            </div>
            @if ($canManage)
                <label class="inline-flex shrink-0 items-center gap-2 text-sm">
                    <input type="hidden" name="forms[{{ $form['form_key'] }}][is_active]" value="0">
                    <input
                        type="checkbox"
                        name="forms[{{ $form['form_key'] }}][is_active]"
                        value="1"
                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        @checked($form['is_active'])
                    >
                    <span class="font-medium text-slate-600">{{ __('Form active') }}</span>
                </label>
            @else
                <x-admin.status-badge :variant="$form['is_active'] ? 'success' : 'danger'">
                    {{ $form['is_active'] ? __('Active') : __('Inactive') }}
                </x-admin.status-badge>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="erp-table erp-table--grid">
            <thead>
                <tr>
                    <th class="pl-4 sm:pl-5">{{ __('Field') }}</th>
                    <th>{{ __('Visibility') }}</th>
                    <th>{{ __('Requirement') }}</th>
                    <th>{{ __('Read only') }}</th>
                    <th>{{ __('Default value') }}</th>
                    @if ($canManage)
                        <th class="pr-4 sm:pr-5 text-right" title="{{ __('Remove is only available for custom fields you added.') }}">{{ __('Actions') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-erp-border bg-white">
                @foreach ($form['fields'] as $field)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2 pl-4 font-medium text-slate-700 sm:pl-5">
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
                        <td class="py-2">
                            @if ($canManage)
                                @php
                                    $visibilityLocked = ($field['required'] && ! ($field['registry_required'] ?? false)) || ($field['registry_required'] ?? false);
                                    $visibilityValue = $field['hidden'] ? 'hidden' : 'visible';
                                @endphp
                                @if ($visibilityLocked)
                                    <input type="hidden" name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][visibility]" value="{{ $visibilityValue }}">
                                @endif
                                <select
                                    name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][visibility]"
                                    class="erp-select form-field-visibility w-full min-w-[7rem] max-w-[9rem]"
                                    data-registry-required="{{ ($field['registry_required'] ?? false) ? '1' : '0' }}"
                                    @disabled($visibilityLocked)
                                >
                                    <option value="visible" @selected($field['visible'])>{{ __('Visible') }}</option>
                                    <option value="hidden" @selected($field['hidden'])>{{ __('Hidden') }}</option>
                                </select>
                                <p @class([
                                    'form-field-visibility-hint mt-1 text-[10px] text-slate-400',
                                    'hidden' => ! ($field['required'] || ($field['registry_required'] ?? false)),
                                ])>{{ __('Required fields stay visible') }}</p>
                            @else
                                <span class="text-slate-600">{{ $field['hidden'] ? __('Hidden') : __('Visible') }}</span>
                            @endif
                        </td>
                        <td class="py-2">
                            @if ($canManage)
                                @if ($field['registry_required'] ?? false)
                                    <input type="hidden" name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][requirement]" value="required">
                                @endif
                                <select
                                    name="forms[{{ $form['form_key'] }}][fields][{{ $field['field_key'] }}][requirement]"
                                    class="erp-select form-field-requirement w-full min-w-[7rem] max-w-[9rem]"
                                    data-registry-required="{{ ($field['registry_required'] ?? false) ? '1' : '0' }}"
                                    @disabled($field['registry_required'] ?? false)
                                >
                                    <option value="required" @selected($field['required'])>{{ __('Required') }}</option>
                                    <option value="optional" @selected(! $field['required']) @disabled($field['registry_required'] ?? false)>{{ __('Optional') }}</option>
                                </select>
                                @if ($field['registry_required'] ?? false)
                                    <p class="mt-1 text-[10px] text-slate-400">{{ __('System field') }}</p>
                                @endif
                            @else
                                <span class="text-slate-600">{{ $field['required'] ? __('Required') : __('Optional') }}</span>
                            @endif
                        </td>
                        <td class="py-2 text-center">
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
                        <td class="py-2">
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
                            <td class="py-2 pr-4 text-right sm:pr-5">
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
                                    <span class="text-xs text-slate-400" title="{{ __('Built-in system fields cannot be removed. Use visibility to hide them instead.') }}">{{ __('Built-in') }}</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($canManage)
        <details class="group border-t border-erp-border bg-violet-50/40">
            <summary class="cursor-pointer list-none px-4 py-2 text-sm font-medium text-erp-primary hover:bg-violet-50/80 sm:px-5 [&::-webkit-details-marker]:hidden">
                <span class="inline-flex items-center gap-2">
                    <x-admin.icon name="chevron-down" class="h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0" />
                    {{ __('Add custom field') }}
                </span>
            </summary>
            <div id="add-custom-field">
                @include('admin.settings.forms.partials.add-custom-field-panel', [
                    'form' => $form,
                    'canManage' => $canManage,
                    'position' => 'bottom',
                    'bare' => true,
                ])
            </div>
        </details>

        <div class="sticky bottom-0 z-10 flex flex-wrap items-center justify-between gap-2 border-t border-erp-border bg-erp-card px-4 py-2 sm:px-5">
            <p class="text-xs text-slate-500">
                {{ __('Built-in fields are system-defined. Custom fields are stored for your tenant.') }}
            </p>
            <x-primary-button
                type="button"
                data-erp-form-settings-save
                data-saving-label="{{ __('Saving…') }}"
            >
                {{ __('Save form settings') }}
            </x-primary-button>
        </div>
    @endif
</x-admin.card>

