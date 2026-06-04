@php
    $queryExceptMonth = request()->except('page', 'month');
@endphp
<x-admin-layout
    :title="__('Delivery Calendar')"
    :breadcrumbs="[
        ['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')],
        ['label' => __('Delivery calendar')],
    ]"
>
    <x-admin.page-header
        :title="__('Delivery Calendar')"
        :description="__('Scheduled deliveries by delivery date.')"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.dispatch.delivery-notes.index') }}" class="erp-btn-secondary">{{ __('Delivery notes') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 flex flex-wrap gap-3 text-sm">
        @foreach (App\Enums\Dispatch\DeliveryNoteStatus::cases() as $status)
            @php
                $count = $calendar['status_counts'][$status->value] ?? 0;
            @endphp
            <span class="inline-flex items-center gap-1.5 rounded-full border border-erp-border bg-white px-2.5 py-1 text-xs text-slate-600">
                <span class="font-medium tabular-nums">{{ $count }}</span>
                {{ $status->label() }}
            </span>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="month" value="{{ $calendar['month'] }}">
        <div>
            <label class="text-xs text-slate-600" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-select mt-1">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filterStatus === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>
    </form>

    <x-admin.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <a
                href="{{ route('admin.dispatch.calendar', array_merge($queryExceptMonth, ['month' => $calendar['prev_month']])) }}"
                class="erp-btn-secondary text-sm"
            >
                {{ __('Previous') }}
            </a>
            <h3 class="text-sm font-semibold text-erp-primary">{{ $calendar['label'] }}</h3>
            <a
                href="{{ route('admin.dispatch.calendar', array_merge($queryExceptMonth, ['month' => $calendar['next_month']])) }}"
                class="erp-btn-secondary text-sm"
            >
                {{ __('Next') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] border-collapse text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-slate-500">
                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <th class="border border-erp-border bg-erp-page px-2 py-2 text-center font-medium">{{ __($weekday) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calendar['weeks'] as $week)
                        <tr>
                            @foreach ($week as $day)
                                <td class="align-top border border-erp-border p-1 min-h-[5rem] w-[14.28%] {{ $day['in_month'] ? 'bg-white' : 'bg-erp-page/60' }} {{ $day['is_today'] ? 'ring-2 ring-inset ring-erp-accent/40' : '' }}">
                                    <div class="mb-1 text-xs font-medium tabular-nums {{ $day['in_month'] ? 'text-slate-700' : 'text-slate-400' }}">
                                        {{ $day['label'] }}
                                    </div>
                                    <ul class="space-y-0.5">
                                        @foreach ($day['notes'] as $note)
                                            <li>
                                                <a
                                                    href="{{ route('admin.dispatch.delivery-notes.show', $note['id']) }}"
                                                    class="block truncate rounded px-1 py-0.5 text-[10px] leading-tight font-medium {{ $note['status_class'] }}"
                                                    title="{{ $note['number'] }} — {{ $note['customer'] }}"
                                                >
                                                    {{ $note['number'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
