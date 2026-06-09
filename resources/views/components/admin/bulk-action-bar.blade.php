@props([
    'formId' => null,
    'checkboxClass' => 'erp-bulk-checkbox',
    'selectAllId' => null,
])

<div
    x-data="{
        selectedCount: 0,
        refresh() {
            this.selectedCount = document.querySelectorAll('.{{ $checkboxClass }}:checked').length;
        },
    }"
    x-init="
        refresh();
        document.addEventListener('change', (event) => {
            if (event.target.matches('.{{ $checkboxClass }}') || event.target.id === '{{ $selectAllId }}') {
                refresh();
            }
        });
    "
    x-show="selectedCount > 0"
    x-cloak
    {{ $attributes->merge(['class' => 'erp-bulk-action-bar mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-erp-border bg-slate-50 px-3 py-2']) }}
>
    <span class="text-xs font-medium text-slate-600" x-text="selectedCount + ' {{ __('selected') }}'"></span>
    {{ $slot }}
</div>
