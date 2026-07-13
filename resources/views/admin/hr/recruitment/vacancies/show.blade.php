<x-admin-layout :title="$vacancy->title">
    <x-admin.page-header :title="$vacancy->title" :description="$vacancy->reference">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ $vacancy->status->label() }}</span>
            @can('update', $vacancy)
                @if ($vacancy->status === \App\Enums\VacancyStatus::Draft)
                    <form method="POST" action="{{ route('admin.hr.recruitment.vacancies.publish', $vacancy) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Publish') }}</button></form>
                @endif
                @if ($vacancy->status === \App\Enums\VacancyStatus::Open)
                    <form method="POST" action="{{ route('admin.hr.recruitment.vacancies.close', $vacancy) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Close') }}</button></form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

<x-admin.card class="mb-6">
        <dl class="grid gap-3 text-sm md:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Department') }}</dt><dd>{{ $vacancy->department?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Job Title') }}</dt><dd>{{ $vacancy->jobTitle?->title ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Positions') }}</dt><dd>{{ $vacancy->filled_count }}/{{ $vacancy->positions }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $vacancy->description ?: '—' }}</dd></div>
        </dl>
    </x-admin.card>

    <x-admin.card :title="__('Applications')">
        @forelse ($vacancy->applications as $application)
            <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                <a href="{{ route('admin.hr.recruitment.applications.show', $application) }}" class="text-erp-primary hover:underline">{{ $application->candidate->full_name }}</a>
                <span class="text-slate-500">· {{ $application->stage->label() }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No applications yet.') }}</p>
        @endforelse
    </x-admin.card>
</x-admin-layout>
