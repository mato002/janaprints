@props(['compliance'])

<x-admin.card :title="__('Training Compliance')">
    <div class="mb-4">
        <p class="text-3xl font-bold tabular-nums text-erp-primary">{{ $compliance['rate'] }}%</p>
        <p class="text-xs text-slate-500">{{ __('Completed assignments vs assigned') }}</p>
    </div>
    <dl class="grid grid-cols-3 gap-3 text-sm">
        <div>
            <dt class="text-xs text-slate-500">{{ __('Assigned') }}</dt>
            <dd class="font-semibold">{{ $compliance['assigned'] }}</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-500">{{ __('Completed') }}</dt>
            <dd class="font-semibold text-emerald-600">{{ $compliance['completed'] }}</dd>
        </div>
        <div>
            <dt class="text-xs text-slate-500">{{ __('Overdue') }}</dt>
            <dd class="font-semibold text-amber-600">{{ $compliance['overdue'] }}</dd>
        </div>
    </dl>
</x-admin.card>
