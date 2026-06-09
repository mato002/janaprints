@props(['can_export', 'filters'])

@if ($can_export)
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['csv' => 'CSV', 'excel' => 'Excel'] as $format => $label)
            <form method="POST" action="{{ route('admin.hr.kpi.export') }}">
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
    </div>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled>
        {{ __('Export') }}
    </button>
@endif
