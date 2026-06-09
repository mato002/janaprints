<x-admin-layout :title="__('Candidate Pipeline')">
    <x-admin.page-header :title="__('Candidate Pipeline')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.recruitment.applications.index') }}" class="erp-btn-secondary">{{ __('List view') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 overflow-x-auto lg:grid-cols-4 xl:grid-cols-8">
        @foreach ($stages as $stage)
            <x-admin.card :title="$stage->label()" class="min-w-[180px]">
                @forelse ($board->get($stage->value, collect()) as $application)
                    <div class="mb-2 rounded border border-erp-border/60 p-2 text-sm">
                        <a href="{{ route('admin.hr.recruitment.applications.show', $application) }}" class="font-medium text-erp-primary hover:underline">{{ $application->candidate->full_name }}</a>
                        <p class="text-xs text-slate-500">{{ $application->vacancy->title }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">{{ __('Empty') }}</p>
                @endforelse
            </x-admin.card>
        @endforeach
    </div>
</x-admin-layout>
