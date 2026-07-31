@props(['report_options', 'report_key'])

<select
    id="commercial-report-type"
    name="report"
    class="erp-toolbar-select min-w-[12rem]"
    aria-label="{{ __('Report type') }}"
    onchange="this.form?.requestSubmit()"
>
    @foreach ($report_options as $option)
        <option value="{{ $option['key'] }}" @selected($report_key === $option['key'])>{{ $option['label'] }}</option>
    @endforeach
</select>
