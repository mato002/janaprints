@props(['can_export', 'filters'])

@if ($can_export)
    <div class="flex flex-wrap gap-2">
        @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $format => $label)
            <form method="POST" action="{{ route('commercial.reports.customers.export', $filters) }}">
                @csrf
                <input type="hidden" name="format" value="{{ $format }}">
                <button type="submit" class="erp-btn-secondary text-xs">{{ __('Export :format', ['format' => $label]) }}</button>
            </form>
        @endforeach
    </div>
@endif
