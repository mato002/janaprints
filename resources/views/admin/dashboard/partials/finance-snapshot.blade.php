@php $fin = $dashboard['finance']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title">{{ __('Finance Snapshot') }}</h2></div>
    <dl class="exec-dl">
        <div class="exec-dl__row"><dt>{{ __('Revenue MTD') }}</dt><dd>{{ $fin['revenue_mtd'] }}</dd></div>
        <div class="exec-dl__row"><dt>{{ __('Expenses MTD') }}</dt><dd class="text-slate-500">{{ $fin['expenses_mtd'] }}</dd></div>
        <div class="exec-dl__row"><dt>{{ __('Profit MTD') }}</dt><dd class="text-slate-500">{{ $fin['profit_mtd'] }}</dd></div>
        <div class="exec-dl__row"><dt>{{ __('Receivables') }}</dt><dd class="text-slate-500">{{ $fin['receivables'] }}</dd></div>
        <div class="exec-dl__row"><dt>{{ __('Payables') }}</dt><dd class="text-slate-500">{{ $fin['payables'] }}</dd></div>
        <div class="exec-dl__row"><dt>{{ __('Cash position') }}</dt><dd class="text-slate-500">{{ $fin['cash_position'] }}</dd></div>
    </dl>
    <p class="mt-1 text-[10px] text-slate-400">{{ __('Revenue uses confirmed sales orders until finance module is live.') }}</p>
</section>
