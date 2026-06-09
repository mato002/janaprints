@props(['can_export', 'filters'])

@if ($can_export)
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $format => $label)
            <form method="POST" action="{{ route('admin.reports.hr.export') }}">
                @csrf
                @foreach ($filters as $key => $value)
                    @if ($value !== null && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <input type="hidden" name="format" value="{{ $format }}">
                <button type="submit" class="erp-btn-secondary text-xs">
                    {{ __('Export :format', ['format' => $label]) }}
                </button>
            </form>
        @endforeach

        <a href="{{ route('admin.reports.hr.print', $filters) }}" target="_blank" class="erp-btn-secondary text-xs">
            {{ __('Print') }}
        </a>
    </div>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled title="{{ __('You do not have permission to export reports') }}">
        {{ __('Export') }}
    </button>
@endif
