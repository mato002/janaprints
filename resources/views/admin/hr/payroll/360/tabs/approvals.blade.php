<x-admin.card>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-erp-primary">{{ __('Approval workflow') }}</h3>
            <p class="text-sm text-slate-600">{{ __('Current status: :status', ['status' => $approvals['status']?->label()]) }}</p>
        </div>
        <span class="erp-badge erp-badge--{{ $approvals['status']?->badgeClass() }}">{{ $approvals['status']?->label() }}</span>
    </div>

    <ol class="space-y-3">
        @foreach ($approvals['timeline'] as $step)
            <li class="flex items-start gap-3 rounded-lg border px-4 py-3 {{ $step['done'] ? 'border-emerald-100 bg-emerald-50/40' : 'border-slate-100 bg-slate-50/40' }}">
                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $step['done'] ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                    {{ $step['done'] ? '✓' : '·' }}
                </span>
                <div class="min-w-0">
                    <p class="font-medium text-slate-900">{{ $step['label'] }}</p>
                    <p class="text-sm text-slate-600">
                        @if ($step['done'])
                            {{ $step['user'] ?? __('System') }} · {{ $step['at']?->format('M j, Y H:i') }}
                        @else
                            {{ __('Pending') }}
                        @endif
                    </p>
                </div>
            </li>
        @endforeach
    </ol>
</x-admin.card>
