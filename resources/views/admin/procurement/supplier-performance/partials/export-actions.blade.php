@props(['can_export', 'filters'])

@if ($can_export)
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $format => $label)
            <form method="POST" action="{{ route('admin.procurement.supplier-performance.export', $filters) }}">
                @csrf
                <input type="hidden" name="format" value="{{ $format }}">
                <button type="submit" class="erp-btn-secondary text-xs">
                    {{ __('Export :format', ['format' => $label]) }}
                </button>
            </form>
        @endforeach
    </div>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled title="{{ __('You do not have permission to export supplier performance reports') }}">
        {{ __('Export') }}
    </button>
@endif
