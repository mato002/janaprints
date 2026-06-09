<x-admin-layout :title="__('Recruitment')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Recruitment')]]">
    <x-admin.page-header :title="__('Recruitment & Hiring')" :description="__('Job requisitions, vacancies, candidate pipeline, interviews, offers, and onboarding.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\Vacancy::class)
                <a href="{{ route('admin.hr.recruitment.vacancies.create') }}" class="erp-btn-primary">{{ __('New vacancy') }}</a>
            @endcan
            <a href="{{ route('admin.hr.recruitment.applications.pipeline') }}" class="erp-btn-secondary">{{ __('Pipeline') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Open Vacancies'), 'value' => $stats['open_vacancies'], 'icon' => 'briefcase'],
            ['label' => __('Active Applications'), 'value' => $stats['active_applications'], 'icon' => 'users'],
            ['label' => __('Upcoming Interviews'), 'value' => $stats['upcoming_interviews'], 'icon' => 'calendar'],
            ['label' => __('Pending Onboarding'), 'value' => $stats['pending_onboarding'], 'icon' => 'clipboard-check'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-admin.card :title="__('Pipeline Snapshot')">
            <div class="grid grid-cols-2 gap-2 text-sm">
                @foreach ($pipeline as $stage => $count)
                    <div class="flex justify-between rounded border border-erp-border/60 px-3 py-2">
                        <span class="text-slate-600">{{ \App\Enums\RecruitmentPipelineStage::from($stage)->label() }}</span>
                        <span class="font-medium tabular-nums">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.hr.recruitment.applications.pipeline') }}" class="erp-btn-secondary mt-4 inline-block text-xs">{{ __('View pipeline') }}</a>
        </x-admin.card>

        <x-admin.card :title="__('Recent Applications')">
            @forelse ($recentApplications as $application)
                <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                    <a href="{{ route('admin.hr.recruitment.applications.show', $application) }}" class="font-medium text-erp-primary hover:underline">
                        {{ $application->candidate->full_name }}
                    </a>
                    <p class="text-xs text-slate-500">{{ $application->vacancy->title }} · {{ $application->stage->label() }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No applications yet.') }}</p>
            @endforelse
        </x-admin.card>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.hr.recruitment.requisitions.index') }}" class="erp-btn-secondary text-xs">{{ __('Requisitions') }}</a>
        <a href="{{ route('admin.hr.recruitment.vacancies.index') }}" class="erp-btn-secondary text-xs">{{ __('Vacancies') }}</a>
        <a href="{{ route('admin.hr.recruitment.applications.index') }}" class="erp-btn-secondary text-xs">{{ __('Applications') }}</a>
    </div>
</x-admin-layout>
