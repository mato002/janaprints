<x-admin.card>
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-xs text-slate-500">{{ __('Employee Number') }}</dt><dd class="font-mono font-medium">{{ $overview['employee_number'] }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Name') }}</dt><dd class="font-medium">{{ $overview['name'] }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Department') }}</dt><dd>{{ $overview['department'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Branch') }}</dt><dd>{{ $overview['branch'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Job Title') }}</dt><dd>{{ $overview['job_title'] ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Supervisor') }}</dt><dd>{{ $supervisor?->full_name ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Employment Status') }}</dt><dd>{{ $overview['employment_status'] ? ucfirst(str_replace('_', ' ', $overview['employment_status'])) : '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Hire Date') }}</dt><dd>{{ $overview['hire_date']?->format('M j, Y') ?? '—' }}</dd></div>
        <div><dt class="text-xs text-slate-500">{{ __('Contact') }}</dt><dd>{{ $overview['email'] ?? $overview['phone'] ?? '—' }}</dd></div>
    </dl>
</x-admin.card>
