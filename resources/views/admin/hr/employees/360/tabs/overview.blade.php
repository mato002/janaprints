@php
    $payrollFieldsTotal = 5;
    $missingPayroll = collect($overview['missing_payroll_fields'] ?? []);
    $missingRecommended = collect($overview['missing_recommended_fields'] ?? []);
    $payrollReady = (bool) ($overview['payroll_profile_complete'] ?? true);
    $payrollPct = (int) round((($payrollFieldsTotal - $missingPayroll->count()) / max($payrollFieldsTotal, 1)) * 100);

    $leaveBalanceDays = collect($leave['balances'] ?? [])->sum(fn ($b) => (float) ($b['available'] ?? 0));
    $pendingLeave = $leave['pending']->count();
    $todayAttendance = null;
    foreach ($attendance['records'] as $record) {
        $date = $record->attendance_date ?? null;
        if ($date && \Illuminate\Support\Carbon::parse($date)->isToday()) {
            $todayAttendance = $record;
            break;
        }
    }
    $nextPayslip = $payroll['payslips']->first();
    $birthdaySoon = false;
    $birthdayLabel = null;
    if ($overview['date_of_birth']) {
        $nextBirthday = $overview['date_of_birth']->copy()->year((int) now()->year)->startOfDay();
        if ($nextBirthday->lt(now()->startOfDay())) {
            $nextBirthday->addYear();
        }
        $daysUntil = (int) now()->startOfDay()->diffInDays($nextBirthday, false);
        if ($daysUntil >= 0 && $daysUntil <= 30) {
            $birthdaySoon = true;
            $birthdayLabel = $daysUntil === 0
                ? __('Today')
                : __('In :days days', ['days' => $daysUntil]);
        }
    }
    $editUrl = route('admin.employees.edit', $employee);
    $canUpdate = auth()->user()->can('update', $employee);
    $hasOpenTasks = $pendingLeave > 0 || $missingPayroll->isNotEmpty() || $missingRecommended->isNotEmpty();
    $hasPendingDocs = $documents['all']->total() === 0 || $missingRecommended->isNotEmpty();
    $showIntel = $todayAttendance
        || $leaveBalanceDays > 0
        || $pendingLeave > 0
        || $nextPayslip
        || $hasOpenTasks
        || $hasPendingDocs
        || $assets['issued']->isNotEmpty()
        || $birthdaySoon
        || $timeline->isNotEmpty();

    $empty = function (?string $value, string $message) use ($canUpdate, $editUrl): array {
        $filled = filled($value);

        return [
            'filled' => $filled,
            'display' => $filled ? $value : $message,
            'empty' => ! $filled,
            'edit' => $canUpdate && ! $filled,
            'url' => $editUrl,
        ];
    };
@endphp

<div class="employee-360__overview">
    <div class="employee-360__overview-main">
        @if ($overview['is_suspended'] ?? false)
            <div class="employee-360__alert employee-360__alert--warning">
                {{ __('This employee is suspended. ERP access is blocked and they are excluded from payroll runs.') }}
            </div>
        @elseif ($overview['access_restricted'] ?? false)
            <div class="employee-360__alert employee-360__alert--neutral">
                {{ __('ERP access is restricted for this employee.') }}
            </div>
        @endif

        <section class="employee-360__card employee-360__card--readiness {{ $payrollReady ? 'employee-360__card--ready' : 'employee-360__card--attention' }}">
            <div class="employee-360__card-head">
                <div class="employee-360__card-title-wrap">
                    <span class="employee-360__card-icon employee-360__card-icon--payroll" aria-hidden="true">
                        <x-admin.icon name="cash" class="h-4 w-4" />
                    </span>
                    <h2 class="employee-360__card-title">{{ __('Payroll Readiness') }}</h2>
                </div>
                <span class="employee-360__readiness-pct">{{ $payrollPct }}%</span>
            </div>

            <div class="employee-360__progress" role="progressbar" aria-valuenow="{{ $payrollPct }}" aria-valuemin="0" aria-valuemax="100">
                <div class="employee-360__progress-fill {{ $payrollReady ? 'is-ready' : 'is-incomplete' }}" style="width: {{ $payrollPct }}%"></div>
            </div>

            @if ($payrollReady)
                <p class="employee-360__readiness-ok">{{ __('All required statutory and bank details are complete.') }}</p>
            @else
                <p class="employee-360__readiness-missing-label">{{ __('Missing') }}</p>
                <ul class="employee-360__missing-list">
                    @foreach ($missingPayroll as $field)
                        <li>
                            @if ($canUpdate)
                                <a href="{{ $editUrl }}" class="employee-360__missing-link" data-erp-modal-open>{{ $field['label'] }}</a>
                            @else
                                {{ $field['label'] }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="employee-360__card-grid">
            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--employment" aria-hidden="true">
                            <x-admin.icon name="identification" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Employment') }}</h2>
                    </div>
                    @if ($canUpdate)
                        <a href="{{ $editUrl }}" class="employee-360__card-action" data-erp-modal-open>{{ __('Edit') }}</a>
                    @endif
                </div>
                <div class="employee-360__fields">
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="badge-check" class="h-3.5 w-3.5" /> {{ __('Employee No.') }}</span>
                        <span class="employee-360__field-value erp-ref-code">{{ $overview['employee_number'] }}</span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="office-building" class="h-3.5 w-3.5" /> {{ __('Department') }}</span>
                        @php $f = $empty($overview['department'] ?? null, __('No department assigned')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="tag" class="h-3.5 w-3.5" /> {{ __('Position') }}</span>
                        @php $f = $empty($overview['job_title'] ?? null, __('No position assigned')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="building" class="h-3.5 w-3.5" /> {{ __('Branch') }}</span>
                        @php $f = $empty($overview['branch'] ?? null, __('No branch assigned')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">{{ $f['display'] }}</span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="users" class="h-3.5 w-3.5" /> {{ __('Supervisor') }}</span>
                        @php $f = $empty($supervisor?->full_name, __('No supervisor assigned')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">{{ $f['display'] }}</span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="calendar" class="h-3.5 w-3.5" /> {{ __('Hire Date') }}</span>
                        <span class="employee-360__field-value">
                            {{ $overview['hire_date']?->format('d M Y') ?? __('No hire date') }}
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="shield-check" class="h-3.5 w-3.5" /> {{ __('Status') }}</span>
                        <span class="employee-360__field-value">
                            {{ $overview['employment_status'] ? ucfirst(str_replace('_', ' ', $overview['employment_status'])) : __('Unknown') }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--personal" aria-hidden="true">
                            <x-admin.icon name="user-circle" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Personal') }}</h2>
                    </div>
                    @if ($canUpdate)
                        <a href="{{ $editUrl }}" class="employee-360__card-action" data-erp-modal-open>{{ __('Edit') }}</a>
                    @endif
                </div>
                <div class="employee-360__fields">
                    <div class="employee-360__field">
                        <span class="employee-360__field-label">{{ __('Gender') }}</span>
                        <span class="employee-360__field-value {{ blank($overview['gender']) ? 'is-empty' : '' }}">
                            {{ $overview['gender'] ? ucfirst($overview['gender']) : __('Not specified') }}
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="calendar" class="h-3.5 w-3.5" /> {{ __('Date of birth') }}</span>
                        @php $f = $empty($overview['date_of_birth']?->format('d M Y'), __('No date of birth')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="identification" class="h-3.5 w-3.5" /> {{ __('National ID') }}</span>
                        @php $f = $empty($overview['national_id'] ?? null, __('No national ID added')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label">{{ __('Personal email') }}</span>
                        @php $f = $empty($overview['email'] ?? null, __('No email added')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">{{ $f['display'] }}</span>
                    </div>
                    <div class="employee-360__field">
                        <span class="employee-360__field-label"><x-admin.icon name="device-mobile" class="h-3.5 w-3.5" /> {{ __('Phone') }}</span>
                        @php $f = $empty($overview['phone'] ?? null, __('No phone added')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                    <div class="employee-360__field employee-360__field--wide">
                        <span class="employee-360__field-label"><x-admin.icon name="location-marker" class="h-3.5 w-3.5" /> {{ __('Address') }}</span>
                        @php $f = $empty($overview['address'] ?? null, __('No address added')); @endphp
                        <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                            {{ $f['display'] }}
                            @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                        </span>
                    </div>
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--statutory" aria-hidden="true">
                            <x-admin.icon name="receipt-tax" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Statutory & Bank') }}</h2>
                    </div>
                    @if ($canUpdate)
                        <a href="{{ $editUrl }}" class="employee-360__card-action" data-erp-modal-open>{{ __('Edit') }}</a>
                    @endif
                </div>
                <div class="employee-360__fields">
                    @foreach ([
                        ['label' => __('KRA PIN'), 'value' => $overview['kra_pin'] ?? null, 'empty' => __('No KRA PIN added')],
                        ['label' => __('NSSF'), 'value' => $overview['nssf_number'] ?? null, 'empty' => __('No NSSF number added')],
                        ['label' => __('SHIF / NHIF'), 'value' => $overview['nhif_number'] ?? null, 'empty' => __('No SHIF/NHIF number added')],
                        ['label' => __('Bank name'), 'value' => $overview['bank_name'] ?? null, 'empty' => __('No bank account added')],
                        ['label' => __('Bank account'), 'value' => $overview['bank_account_number'] ?? null, 'empty' => __('No bank account added')],
                        ['label' => __('Branch code'), 'value' => $overview['bank_branch_code'] ?? null, 'empty' => __('No branch code added')],
                    ] as $row)
                        @php $f = $empty($row['value'], $row['empty']); @endphp
                        <div class="employee-360__field">
                            <span class="employee-360__field-label">{{ $row['label'] }}</span>
                            <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                                {{ $f['display'] }}
                                @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--emergency" aria-hidden="true">
                            <x-admin.icon name="bell" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Emergency & Contacts') }}</h2>
                    </div>
                    @if ($canUpdate)
                        <a href="{{ $editUrl }}" class="employee-360__card-action" data-erp-modal-open>{{ __('Edit') }}</a>
                    @endif
                </div>
                <div class="employee-360__fields">
                    @foreach ([
                        ['label' => __('Emergency contact'), 'value' => $overview['emergency_contact_name'] ?? null, 'empty' => __('No emergency contact')],
                        ['label' => __('Emergency phone'), 'value' => $overview['emergency_contact_phone'] ?? null, 'empty' => __('No emergency phone')],
                        ['label' => __('Next of kin'), 'value' => $overview['next_of_kin_name'] ?? null, 'empty' => __('No next of kin')],
                        ['label' => __('Next of kin phone'), 'value' => $overview['next_of_kin_phone'] ?? null, 'empty' => __('No next of kin phone')],
                        ['label' => __('Relationship'), 'value' => $overview['next_of_kin_relationship'] ?? null, 'empty' => __('No relationship set')],
                    ] as $row)
                        @php $f = $empty($row['value'], $row['empty']); @endphp
                        <div class="employee-360__field">
                            <span class="employee-360__field-label">{{ $row['label'] }}</span>
                            <span class="employee-360__field-value {{ $f['empty'] ? 'is-empty' : '' }}">
                                {{ $f['display'] }}
                                @if ($f['edit']) <a href="{{ $f['url'] }}" class="employee-360__inline-link" data-erp-modal-open>{{ __('Add') }}</a> @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--assets" aria-hidden="true">
                            <x-admin.icon name="cube" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Assets') }}</h2>
                    </div>
                    <button type="button" class="employee-360__card-action" @click="setTab('assets')">{{ __('View') }}</button>
                </div>
                @if ($assets['issued']->isNotEmpty())
                    <ul class="employee-360__compact-list">
                        @foreach ($assets['issued']->take(4) as $asset)
                            <li>
                                <span class="employee-360__compact-title">{{ $asset->asset_name ?? $asset->asset_number }}</span>
                                <span class="employee-360__compact-meta">{{ $asset->asset_number }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if ($assets['issued']->count() > 4)
                        <p class="employee-360__more-count">{{ __('+:count more', ['count' => $assets['issued']->count() - 4]) }}</p>
                    @endif
                @else
                    <p class="employee-360__empty-block">{{ __('No assets issued') }}</p>
                @endif
            </section>

            <section class="employee-360__card">
                <div class="employee-360__card-head">
                    <div class="employee-360__card-title-wrap">
                        <span class="employee-360__card-icon employee-360__card-icon--docs" aria-hidden="true">
                            <x-admin.icon name="document-text" class="h-4 w-4" />
                        </span>
                        <h2 class="employee-360__card-title">{{ __('Documents') }}</h2>
                    </div>
                    <button type="button" class="employee-360__card-action" @click="setTab('documents')">{{ __('View') }}</button>
                </div>
                <div class="employee-360__stat-row">
                    <div>
                        <span class="employee-360__stat-value">{{ $documents['all']->total() }}</span>
                        <span class="employee-360__stat-label">{{ __('On file') }}</span>
                    </div>
                    <div>
                        <span class="employee-360__stat-value">{{ $missingRecommended->count() }}</span>
                        <span class="employee-360__stat-label">{{ __('Profile gaps') }}</span>
                    </div>
                </div>
                @if ($missingRecommended->isNotEmpty())
                    <ul class="employee-360__missing-list employee-360__missing-list--compact">
                        @foreach ($missingRecommended->take(3) as $field)
                            <li>
                                @if ($canUpdate)
                                    <a href="{{ $editUrl }}" class="employee-360__missing-link" data-erp-modal-open>{{ $field['label'] }}</a>
                                @else
                                    {{ $field['label'] }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="employee-360__readiness-ok">{{ __('Recommended profile fields are complete.') }}</p>
                @endif
            </section>
        </div>
    </div>

    @if ($showIntel)
        <aside class="employee-360__intel" aria-label="{{ __('Employee intelligence') }}">
            <h2 class="employee-360__intel-heading">{{ __('Intelligence') }}</h2>

            @if ($todayAttendance)
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __("Today's Attendance") }}</h3>
                    <p class="employee-360__widget-value">
                        {{ $todayAttendance->status instanceof \App\Enums\AttendanceStatus ? $todayAttendance->status->label() : ucfirst((string) $todayAttendance->status) }}
                    </p>
                    @if ($todayAttendance->clock_in_at)
                        <p class="employee-360__widget-meta">{{ __('In') }} {{ $todayAttendance->clock_in_at->format('H:i') }}</p>
                    @endif
                </section>
            @endif

            @if ($leaveBalanceDays > 0 || $pendingLeave > 0)
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __('Leave Balance') }}</h3>
                    <p class="employee-360__widget-value">{{ number_format($leaveBalanceDays, 1) }} {{ __('days') }}</p>
                    @if ($pendingLeave > 0)
                        <p class="employee-360__widget-meta employee-360__widget-meta--warn">
                            {{ __(':count pending request(s)', ['count' => $pendingLeave]) }}
                        </p>
                    @endif
                    <button type="button" class="employee-360__widget-link" @click="setTab('leave')">{{ __('Open leave') }}</button>
                </section>
            @endif

            @if ($nextPayslip)
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __('Latest Payroll') }}</h3>
                    <p class="employee-360__widget-value">{{ number_format((float) $nextPayslip->net_pay, 0) }}</p>
                    <p class="employee-360__widget-meta">
                        {{ $nextPayslip->payrollRun?->period_end?->format('M Y') ?? $nextPayslip->created_at?->format('M Y') }}
                    </p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('payroll')">{{ __('View payslips') }}</button>
                </section>
            @endif

            @if ($hasOpenTasks)
                <section class="employee-360__widget employee-360__widget--tasks">
                    <h3 class="employee-360__widget-title">{{ __('Open Tasks') }}</h3>
                    <ul class="employee-360__task-list">
                        @if ($missingPayroll->isNotEmpty())
                            <li>{{ __('Complete payroll profile (:count)', ['count' => $missingPayroll->count()]) }}</li>
                        @endif
                        @if ($pendingLeave > 0)
                            <li>{{ __('Review :count leave request(s)', ['count' => $pendingLeave]) }}</li>
                        @endif
                        @if ($missingRecommended->isNotEmpty())
                            <li>{{ __('Fill :count recommended fields', ['count' => $missingRecommended->count()]) }}</li>
                        @endif
                    </ul>
                </section>
            @endif

            @if ($hasPendingDocs)
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __('Pending Documents') }}</h3>
                    <p class="employee-360__widget-value">{{ $documents['all']->total() }} {{ __('on file') }}</p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('documents')">{{ __('Manage documents') }}</button>
                </section>
            @endif

            @if ($assets['issued']->isNotEmpty())
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __('Assets') }}</h3>
                    <p class="employee-360__widget-value">{{ $assets['issued']->count() }} {{ __('issued') }}</p>
                    <button type="button" class="employee-360__widget-link" @click="setTab('assets')">{{ __('View assets') }}</button>
                </section>
            @endif

            @if ($birthdaySoon)
                <section class="employee-360__widget employee-360__widget--celebrate">
                    <h3 class="employee-360__widget-title">{{ __('Upcoming Birthday') }}</h3>
                    <p class="employee-360__widget-value">{{ $birthdayLabel }}</p>
                    <p class="employee-360__widget-meta">{{ $overview['date_of_birth']->format('d M') }}</p>
                </section>
            @endif

            @if ($timeline->isNotEmpty())
                <section class="employee-360__widget">
                    <h3 class="employee-360__widget-title">{{ __('Recent Activity') }}</h3>
                    <ul class="employee-360__timeline-mini">
                        @foreach ($timeline->take(4) as $event)
                            <li>
                                <span class="employee-360__timeline-mini-title">{{ $event->title }}</span>
                                <span class="employee-360__timeline-mini-date">{{ $event->eventDatetime->format('d M') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="employee-360__widget-link" @click="setTab('timeline')">{{ __('Full timeline') }}</button>
                </section>
            @endif
        </aside>
    @endif
</div>
