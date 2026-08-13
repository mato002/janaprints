@php
    use App\Enums\ProductionDestination;

    $name = $name ?? 'production_destination';
    $alpineModel = $alpineModel ?? null;
    $selected = old($name, $value ?? '');
    $required = $required ?? true;
    $editable = $editable ?? true;
@endphp

<fieldset class="space-y-2">
    <legend class="erp-label">
        {{ __('Where is this order going?') }}
        @if ($required)
            <span class="text-red-600">*</span>
        @endif
    </legend>
    <p class="text-xs text-slate-500">{{ __('Production is Digital, Offset, or Outsourced. Choose the lane before creating the order.') }}</p>

    @if ($alpineModel)
        <input type="hidden" name="{{ $name }}" :value="{{ $alpineModel }}">
    @endif

    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
        @foreach (ProductionDestination::cases() as $destination)
            @if ($alpineModel)
                <button
                    type="button"
                    @disabled(! $editable)
                    @click="{{ $alpineModel }} = @js($destination->value)"
                    :class="{{ $alpineModel }} === @js($destination->value)
                        ? 'border-erp-accent bg-erp-accent/5 ring-1 ring-erp-accent'
                        : 'border-erp-border bg-white hover:border-erp-accent/40'"
                    class="rounded-lg border px-3 py-3 text-left disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span class="block text-sm font-semibold text-slate-900">{{ $destination->label() }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $destination->hint() }}</span>
                </button>
            @else
                <label
                    class="cursor-pointer rounded-lg border px-3 py-3 text-left border-erp-border bg-white hover:border-erp-accent/40 has-[:checked]:border-erp-accent has-[:checked]:bg-erp-accent/5 has-[:checked]:ring-1 has-[:checked]:ring-erp-accent {{ $editable ? '' : 'pointer-events-none opacity-60' }}"
                >
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $destination->value }}"
                        class="sr-only"
                        @checked($selected === $destination->value)
                        @disabled(! $editable)
                        @required($required)
                    >
                    <span class="block text-sm font-semibold text-slate-900">{{ $destination->label() }}</span>
                    <span class="mt-0.5 block text-xs text-slate-500">{{ $destination->hint() }}</span>
                </label>
            @endif
        @endforeach
    </div>

    @error($name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</fieldset>
