<section class="crm-360__kpi-strip" aria-label="{{ __('Customer KPIs') }}">
    @foreach ($kpis as $kpi)
        @php
            $display = '—';
            $sub = $kpi['hint'] ?? null;
            if (($kpi['format'] ?? null) === 'money' && $kpi['value'] !== null) {
                $display = number_format((float) $kpi['value'], 2);
            } elseif (($kpi['format'] ?? null) === 'date' && $kpi['value']) {
                $display = $kpi['value']->diffForHumans();
                $sub = $kpi['value']->format('d M Y H:i');
            } elseif ($kpi['value'] !== null && $kpi['value'] !== '') {
                $display = (string) $kpi['value'];
            } else {
                $sub = $sub ?? __('No data yet');
            }
            $priority = $kpi['priority'] ?? 'medium';
            $trend = $kpi['trend'] ?? null;
        @endphp
        <article class="crm-360__kpi crm-360__kpi--{{ $priority }}">
            <div class="crm-360__kpi-top">
                <span class="crm-360__kpi-icon crm-360__kpi-icon--{{ $kpi['icon'] ?? 'default' }}" aria-hidden="true">
                    @switch($kpi['icon'] ?? 'default')
                        @case('revenue')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @case('balance')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @break
                        @case('chat')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            @break
                        @case('quote')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @break
                        @case('order')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            @break
                        @case('activity')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @break
                        @default
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endswitch
                </span>
                @if ($trend === 'up')
                    <span class="crm-360__kpi-trend crm-360__kpi-trend--up" title="{{ __('Active') }}">↑</span>
                @elseif ($trend === 'alert')
                    <span class="crm-360__kpi-trend crm-360__kpi-trend--alert" title="{{ __('Attention') }}">!</span>
                @endif
            </div>
            <span class="crm-360__kpi-label">{{ $kpi['label'] }}</span>
            <span class="crm-360__kpi-value {{ $display === '—' ? 'crm-360__kpi-value--empty' : '' }}">{{ $display }}</span>
            @if ($sub)
                <span class="crm-360__kpi-hint">{{ $sub }}</span>
            @endif
        </article>
    @endforeach
</section>
