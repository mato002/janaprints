@props(['can_export', 'filters', 'schedule_frequencies'])

@if ($can_export)
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['csv' => 'CSV', 'excel' => 'Excel', 'pdf' => 'PDF'] as $format => $label)
            <form method="POST" action="{{ route('admin.reports.production.export', $filters) }}">
                @csrf
                <input type="hidden" name="format" value="{{ $format }}">
                <button type="submit" class="erp-btn-secondary text-xs">
                    {{ __('Export :format', ['format' => $label]) }}
                </button>
            </form>
        @endforeach

        <details class="relative">
            <summary class="erp-btn-secondary cursor-pointer text-xs list-none">{{ __('Schedule Export') }}</summary>
            <div class="absolute right-0 z-10 mt-2 w-64 rounded-lg border border-erp-border bg-white p-3 shadow-lg">
                <form method="POST" action="{{ route('admin.reports.production.export', $filters) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="schedule" value="1">
                    <div>
                        <label class="text-[11px] text-slate-500" for="schedule_format">{{ __('Format') }}</label>
                        <select id="schedule_format" name="format" class="erp-input mt-1 w-full">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] text-slate-500" for="schedule_frequency">{{ __('Frequency') }}</label>
                        <select id="schedule_frequency" name="frequency" class="erp-input mt-1 w-full">
                            @foreach ($schedule_frequencies as $key => $label)
                                <option value="{{ $key }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="erp-btn-primary w-full text-xs">{{ __('Save Schedule') }}</button>
                </form>
            </div>
        </details>

        <a href="{{ route('admin.reports.production.print', $filters) }}" target="_blank" class="erp-btn-secondary text-xs">
            {{ __('Print') }}
        </a>
    </div>
@else
    <button type="button" class="erp-btn-secondary opacity-60" disabled title="{{ __('You do not have permission to export reports') }}">
        {{ __('Export') }}
    </button>
@endif
