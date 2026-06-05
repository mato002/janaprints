<x-admin-layout :title="__('POS Certification')">
    <x-admin.page-header
        :title="__('POS Operational Certification')"
        :description="__('Control certification for inventory, accounting, cash, returns, sessions, and branch compliance. Not intelligence. Not reporting.')"
    />

    <x-admin.card class="mb-6">
        <form method="GET" action="{{ route('admin.commercial.pos.certification.index') }}" class="grid gap-3 md:grid-cols-4">
            <div>
                <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
                <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] ?? $certification['scope']->fromDate->toDateString() }}" class="erp-input mt-1 w-full">
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
                <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] ?? $certification['scope']->toDate->toDateString() }}" class="erp-input mt-1 w-full">
            </div>
            @if ($canViewAllBranches)
                <div>
                    <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
                    <select id="branch_id" name="branch_id" class="erp-input mt-1 w-full">
                        <option value="">{{ __('All branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex items-end">
                <button type="submit" class="erp-btn-primary">{{ __('Run certification') }}</button>
            </div>
        </form>
    </x-admin.card>

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-1">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Certification Score') }}</p>
            <p class="mt-2 text-4xl font-bold tabular-nums {{ $certification['passed'] ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $certification['score'] }}%
            </p>
            <p class="mt-3 inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $certification['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ $certification['verdict'] }}
            </p>
            <p class="mt-4 text-xs text-slate-500">
                {{ __('Certified at :time', ['time' => \Illuminate\Support\Carbon::parse($certification['certified_at'])->format('Y-m-d H:i')]) }}
            </p>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Intended users') }}</h3>
            <div class="flex flex-wrap gap-2 text-sm">
                <span class="rounded-full bg-slate-100 px-3 py-1">{{ __('Operations Manager') }}</span>
                <span class="rounded-full bg-slate-100 px-3 py-1">{{ __('Finance Manager') }}</span>
                <span class="rounded-full bg-slate-100 px-3 py-1">{{ __('Internal Auditor') }}</span>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                {{ __('Scope: :from to :to', [
                    'from' => $certification['scope']->fromDate->format('Y-m-d'),
                    'to' => $certification['scope']->toDate->format('Y-m-d'),
                ]) }}
            </p>
        </x-admin.card>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($certification['domains'] as $domain)
            <x-admin.card>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-erp-primary">{{ $domain['label'] }}</h3>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $domain['passed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $domain['verdict'] }}
                    </span>
                </div>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    @foreach ($domain['metrics'] as $label => $value)
                        <div>
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="font-medium tabular-nums">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if (! empty($domain['issues']))
                    <ul class="mt-3 space-y-1 border-t border-erp-border pt-3 text-xs text-rose-700">
                        @foreach ($domain['issues'] as $issue)
                            <li>• {{ $issue }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 border-t border-erp-border pt-3 text-xs text-emerald-700">{{ __('All checks passed for this domain.') }}</p>
                @endif
            </x-admin.card>
        @endforeach
    </div>
</x-admin-layout>
