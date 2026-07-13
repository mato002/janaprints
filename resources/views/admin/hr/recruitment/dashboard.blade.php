@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin-layout :title="__('Recruitment')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Recruitment')]]">
    <x-admin.page-header :title="__('Recruitment & Hiring')" :description="__('Job requisitions, vacancies, candidate pipeline, interviews, offers, and onboarding.')">
        <x-slot name="actions">
            @if (($tab ?? 'pipeline') === 'requisitions')
                @can('create', App\Models\Hr\JobRequisition::class)
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.requisitions.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New requisition') }}</a>
                @endcan
            @elseif (($tab ?? 'pipeline') === 'vacancies')
                @can('create', App\Models\Hr\Vacancy::class)
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.vacancies.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New vacancy') }}</a>
                @endcan
            @else
                @can('create', App\Models\Hr\JobApplication::class)
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.applications.create')) }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('New application') }}</a>
                @endcan
                @can('create', App\Models\Hr\Vacancy::class)
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.vacancies.create')) }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New vacancy') }}</a>
                @endcan
            @endif
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.dashboard', WorkspaceEmbed::queryParams(['tab' => 'vacancies']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('Open Vacancies')" :value="$stats['open_vacancies']" icon="briefcase" />
        </a>
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.dashboard', WorkspaceEmbed::queryParams(['tab' => 'applications']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('Active Applications')" :value="$stats['active_applications']" icon="users" />
        </a>
        <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.dashboard', WorkspaceEmbed::queryParams(['tab' => 'pipeline']))) }}" data-turbo-frame="{{ $turboFrame }}" class="block rounded-lg transition hover:ring-2 hover:ring-erp-accent/30">
            <x-admin.kpi-widget :label="__('Upcoming Interviews')" :value="$stats['upcoming_interviews']" icon="calendar" />
        </a>
        <x-admin.kpi-widget :label="__('Pending Onboarding')" :value="$stats['pending_onboarding']" icon="clipboard-check" />
    </div>

    <nav class="mt-6 mb-4 flex flex-wrap gap-2 border-b border-slate-200 pb-2" aria-label="{{ __('Recruitment sections') }}">
        @php
            $recruitmentTabs = [
                'pipeline' => __('Pipeline'),
                'applications' => __('Applications'),
                'vacancies' => __('Vacancies'),
                'requisitions' => __('Requisitions'),
            ];
        @endphp
        @foreach ($recruitmentTabs as $id => $label)
            <a
                href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.dashboard', WorkspaceEmbed::queryParams(['tab' => $id]))) }}"
                data-turbo-frame="{{ $turboFrame }}"
                @class([
                    'rounded-md px-3 py-1.5 text-sm font-medium',
                    'bg-erp-primary text-white' => $tab === $id,
                    'text-slate-600 hover:bg-slate-100' => $tab !== $id,
                ])
            >{{ $label }}</a>
        @endforeach
    </nav>

    @if ($tab === 'applications')
        @include('admin.hr.recruitment.partials.workspace-applications')
    @elseif ($tab === 'vacancies')
        @include('admin.hr.recruitment.partials.workspace-vacancies')
    @elseif ($tab === 'requisitions')
        @include('admin.hr.recruitment.partials.workspace-requisitions')
    @else
        @include('admin.hr.recruitment.partials.workspace-pipeline')
    @endif
</x-admin-layout>
