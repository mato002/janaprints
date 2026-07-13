@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<div class="grid gap-4 overflow-x-auto lg:grid-cols-4 xl:grid-cols-8">
    @foreach ($stages as $stage)
        <x-admin.card :title="$stage->label()" class="min-w-[180px]">
            @forelse ($board->get($stage->value, collect()) as $application)
                <div class="mb-2 rounded border border-erp-border/60 p-2 text-sm">
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.applications.show', $application)) }}" class="font-medium text-erp-primary hover:underline">{{ $application->candidate->full_name }}</a>
                    <p class="text-xs text-slate-500">{{ $application->vacancy->title }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-400">{{ __('Empty') }}</p>
            @endforelse
        </x-admin.card>
    @endforeach
</div>

@if (($recentApplications ?? collect())->isNotEmpty())
    <x-admin.card class="mt-6" :title="__('Recent Applications')">
        @foreach ($recentApplications as $application)
            <div class="border-b border-slate-100 py-2 text-sm last:border-0">
                <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.applications.show', $application)) }}" class="font-medium text-erp-primary hover:underline">
                    {{ $application->candidate->full_name }}
                </a>
                <p class="text-xs text-slate-500">{{ $application->vacancy->title }} · {{ $application->stage->label() }}</p>
            </div>
        @endforeach
    </x-admin.card>
@endif
