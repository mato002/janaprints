@props(['form', 'canManage'])

@if ($form['has_status_options'] ?? false)
    <details class="group border-t border-erp-border bg-sky-50/40" open>
        <summary class="cursor-pointer list-none px-4 py-2 text-sm font-medium text-erp-primary hover:bg-sky-50/80 sm:px-5 [&::-webkit-details-marker]:hidden">
            <span class="inline-flex items-center gap-2">
                <x-admin.icon name="chevron-down" class="h-4 w-4 -rotate-90 text-slate-400 transition-transform group-open:rotate-0" />
                {{ __('Status dropdown options') }}
            </span>
        </summary>

        <div class="border-t border-erp-border px-4 py-3 sm:px-5">
            <p class="mb-3 text-xs text-slate-600">
                {{ __('Manage the values shown in this form\'s status dropdown. System statuses cannot be removed, but you can add custom statuses for your company.') }}
            </p>

            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid w-full">
                    <thead>
                        <tr>
                            <th>{{ __('Value') }}</th>
                            <th>{{ __('Label') }}</th>
                            <th>{{ __('Active') }}</th>
                            @if ($canManage)
                                <th class="text-right">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-erp-border bg-white" data-status-options-body>
                        @foreach ($form['status_options'] as $index => $option)
                            <tr>
                                <td class="py-2">
                                    @if ($option['is_system'])
                                        <input type="hidden" name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][value]" value="{{ $option['value'] }}">
                                        <span class="font-mono text-sm text-slate-700">{{ $option['value'] }}</span>
                                        <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-500">{{ __('System') }}</span>
                                    @elseif ($canManage)
                                        <input
                                            type="text"
                                            name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][value]"
                                            value="{{ $option['value'] }}"
                                            class="erp-input w-full min-w-[8rem] font-mono text-sm"
                                            pattern="[a-z0-9_]+"
                                            placeholder="on_hold"
                                        >
                                    @else
                                        <span class="font-mono text-sm text-slate-700">{{ $option['value'] }}</span>
                                    @endif
                                </td>
                                <td class="py-2">
                                    @if ($canManage)
                                        <input
                                            type="text"
                                            name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][label]"
                                            value="{{ $option['label'] }}"
                                            class="erp-input w-full min-w-[10rem]"
                                            required
                                        >
                                    @else
                                        <span class="text-slate-700">{{ $option['label'] }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-center">
                                    @if ($canManage)
                                        <input type="hidden" name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][is_active]" value="0">
                                        <input
                                            type="checkbox"
                                            name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][is_active]"
                                            value="1"
                                            class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                            @checked($option['is_active'])
                                        >
                                    @else
                                        <span class="text-slate-600">{{ $option['is_active'] ? __('Yes') : __('No') }}</span>
                                    @endif
                                </td>
                                @if ($canManage)
                                    <td class="py-2 text-right">
                                        @if (! $option['is_system'])
                                            <label class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                                                <input
                                                    type="checkbox"
                                                    name="forms[{{ $form['form_key'] }}][status_options][{{ $index }}][remove]"
                                                    value="1"
                                                    class="rounded border-red-300 text-red-600 focus:ring-red-500"
                                                >
                                                {{ __('Remove') }}
                                            </label>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('Built-in') }}</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($canManage)
                <button
                    type="button"
                    class="mt-3 inline-flex items-center gap-1 rounded-md border border-dashed border-erp-border px-3 py-1.5 text-xs font-medium text-erp-accent hover:border-erp-accent hover:bg-white"
                    data-add-status-option
                    data-form-key="{{ $form['form_key'] }}"
                    data-next-index="{{ count($form['status_options']) }}"
                >
                    <x-admin.icon name="plus" class="h-3.5 w-3.5" />
                    {{ __('Add status') }}
                </button>

                <template id="status-option-row-template-{{ $form['form_key'] }}">
                    <tr>
                        <td class="py-2">
                            <input
                                type="text"
                                name="forms[{{ $form['form_key'] }}][status_options][__INDEX__][value]"
                                class="erp-input w-full min-w-[8rem] font-mono text-sm"
                                pattern="[a-z0-9_]+"
                                placeholder="on_hold"
                                required
                            >
                        </td>
                        <td class="py-2">
                            <input
                                type="text"
                                name="forms[{{ $form['form_key'] }}][status_options][__INDEX__][label]"
                                class="erp-input w-full min-w-[10rem]"
                                placeholder="{{ __('On hold') }}"
                                required
                            >
                        </td>
                        <td class="py-2 text-center">
                            <input type="hidden" name="forms[{{ $form['form_key'] }}][status_options][__INDEX__][is_active]" value="0">
                            <input
                                type="checkbox"
                                name="forms[{{ $form['form_key'] }}][status_options][__INDEX__][is_active]"
                                value="1"
                                class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                checked
                            >
                        </td>
                        <td class="py-2 text-right">
                            <button type="button" class="text-xs font-medium text-red-600 hover:text-red-700" data-remove-status-row>
                                {{ __('Remove') }}
                            </button>
                        </td>
                    </tr>
                </template>

                <script>
                    document.getElementById('form-panel-{{ $form['form_key'] }}')?.querySelector('[data-add-status-option]')?.addEventListener('click', (event) => {
                        const button = event.currentTarget;
                        const formKey = button.dataset.formKey;
                        const template = document.getElementById(`status-option-row-template-${formKey}`);
                        const body = button.closest('details')?.querySelector('[data-status-options-body]');

                        if (! template || ! body) {
                            return;
                        }

                        const index = Number(button.dataset.nextIndex || body.children.length);
                        const row = template.content.cloneNode(true);

                        row.querySelectorAll('[name]').forEach((input) => {
                            input.name = input.name.replace('__INDEX__', String(index));
                        });

                        body.appendChild(row);
                        button.dataset.nextIndex = String(index + 1);
                    });

                    document.getElementById('form-panel-{{ $form['form_key'] }}')?.addEventListener('click', (event) => {
                        const removeButton = event.target.closest('[data-remove-status-row]');

                        if (! removeButton) {
                            return;
                        }

                        removeButton.closest('tr')?.remove();
                    });
                </script>
            @endif
        </div>
    </details>
@endif
