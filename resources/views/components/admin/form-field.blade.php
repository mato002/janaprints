@props([
    'name',
    'label' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'colSpan' => 1,
])

@if ($visible)
    <div @class([
        'erp-form-field',
        'md:col-span-2' => (int) $colSpan === 2,
    ])>
        @if ($label)
            <x-input-label :for="$name" :value="$label" :required="$required" />
        @endif

        @if ($help)
            <p class="mt-0.5 text-xs text-erp-muted">{{ $help }}</p>
        @endif

        <div @class(['mt-1' => $label !== null])>
            {{ $slot }}
        </div>

        <x-admin.field-error :name="$name" />
    </div>
@endif
