@props(['kpis', 'title' => __('KPIs')])

@include('admin.commercial.reports.partials.kpi-strip', ['kpis' => $kpis])
