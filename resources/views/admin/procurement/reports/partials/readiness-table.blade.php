@props(['readiness', 'report_ready'])

@include('admin.commercial.reports.sales.partials.readiness-table', [
    'readiness' => $readiness,
    'report_ready' => $report_ready,
    'context' => __('procurement reports'),
])
