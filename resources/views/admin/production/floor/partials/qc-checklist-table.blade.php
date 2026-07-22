@php
    $itemsExpression = $itemsExpression ?? 'actionModalPanel?.quality?.checklist_items';
@endphp

<div x-show="({{ $itemsExpression }} ?? []).length > 0" x-cloak>
    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Checklist') }}</h4>
    <div class="overflow-x-auto rounded border border-erp-border">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th class="w-16 text-center">{{ __('Pass') }}</th>
                    <th class="w-16 text-center">{{ __('Fail') }}</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in {{ $itemsExpression }} ?? []" :key="`${index}-${item.label}`">
                    <tr>
                        <td>
                            <span x-text="item.label"></span>
                            <input type="hidden" :name="`checklist[${index}][line_id]`" :value="item.line_id ?? ''">
                            <input type="hidden" :name="`checklist[${index}][label]`" :value="item.label">
                        </td>
                        <td class="text-center">
                            <input type="radio" :name="`checklist[${index}][passed]`" value="1" class="rounded-full border-slate-300">
                        </td>
                        <td class="text-center">
                            <input type="radio" :name="`checklist[${index}][passed]`" value="0" class="rounded-full border-slate-300">
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
