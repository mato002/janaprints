<x-admin-layout :title="__('Leave Request')" :breadcrumbs="[['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => $request->reference ?? __('Request')]]">
    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-erp-primary">{{ $request->employee?->full_name }}</h2>
                    <p class="text-sm text-slate-600">{{ $request->leaveType?->name }} · {{ $request->reference }}</p>
                </div>
                <span class="erp-badge erp-badge--{{ $request->status?->badgeClass() }}">{{ $request->status?->label() }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-500">{{ __('Period') }}</dt><dd class="font-medium">{{ $request->start_date?->format('M j, Y') }} – {{ $request->end_date?->format('M j, Y') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Days requested') }}</dt><dd class="font-medium">{{ $request->days_requested }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $request->branch?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Department') }}</dt><dd>{{ $request->department?->name ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-slate-500">{{ __('Reason') }}</dt><dd>{{ $request->reason }}</dd></div>
            </dl>

            @if (! empty($request->conflict_warnings))
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-semibold">{{ __('Conflict warnings') }}</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($request->conflict_warnings as $warning)
                            <li>{{ $warning['message'] ?? '' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($request->rejection_reason)
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                    <p class="font-semibold">{{ __('Rejection reason') }}</p>
                    <p>{{ $request->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <x-admin.card>
                <h3 class="text-sm font-semibold text-erp-primary mb-3">{{ __('Leave balance') }}</h3>
                <dl class="space-y-2 text-sm">
                    @foreach ($balanceSummary as $key => $value)
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __(ucwords(str_replace('_', ' ', $key))) }}</dt>
                            <dd class="font-medium tabular-nums">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-sm font-semibold text-erp-primary mb-3">{{ __('Actions') }}</h3>
                <div class="space-y-2">
                    @can('approve', $request)
                        @if ($request->status === App\Enums\LeaveRequestStatus::Submitted && $request->leaveType?->requires_supervisor_approval)
                            <form method="POST" action="{{ route('admin.hr.leave.approve.supervisor', $request) }}">
                                @csrf
                                <button type="submit" class="erp-btn-primary w-full">{{ __('Supervisor approve') }}</button>
                            </form>
                        @endif
                        @if (in_array($request->status, [App\Enums\LeaveRequestStatus::Submitted, App\Enums\LeaveRequestStatus::SupervisorApproved]))
                            <form method="POST" action="{{ route('admin.hr.leave.approve.hr', $request) }}">
                                @csrf
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('HR approve') }}</button>
                            </form>
                        @endif
                    @endcan
                    @can('reject', $request)
                        @if (in_array($request->status, [App\Enums\LeaveRequestStatus::Submitted, App\Enums\LeaveRequestStatus::SupervisorApproved]))
                            <form method="POST" action="{{ route('admin.hr.leave.reject', $request) }}" class="space-y-2">
                                @csrf
                                <textarea name="rejection_reason" rows="2" class="erp-input w-full text-sm" placeholder="{{ __('Rejection reason') }}" required></textarea>
                                <button type="submit" class="erp-btn-secondary w-full text-rose-700">{{ __('Reject') }}</button>
                            </form>
                        @endif
                    @endcan
                    @can('create', App\Models\Hr\LeaveRequest::class)
                        @if (! in_array($request->status, [App\Enums\LeaveRequestStatus::Cancelled, App\Enums\LeaveRequestStatus::Rejected]))
                            <form method="POST" action="{{ route('admin.hr.leave.cancel', $request) }}" onsubmit="return confirm(@js(__('Cancel this leave request?')))">
                                @csrf
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('Cancel request') }}</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin-layout>
