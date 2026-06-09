@props(['pipeline'])

<x-admin.card :title="__('Recruitment Pipeline')">
    <p class="mb-3 text-2xl font-bold tabular-nums text-erp-primary">{{ $pipeline['active'] }}</p>
    <p class="mb-4 text-xs text-slate-500">{{ __('Active applications in pipeline') }}</p>
    @if (! empty($pipeline['stages']))
        <div class="grid grid-cols-2 gap-2 text-sm">
            @foreach ($pipeline['stages'] as $stage => $count)
                @if ($count > 0)
                    <div class="flex justify-between rounded border border-erp-border/60 px-2 py-1.5">
                        <span class="text-slate-600">{{ \App\Enums\RecruitmentPipelineStage::from($stage)->label() }}</span>
                        <span class="font-medium tabular-nums">{{ $count }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
    @can('hr.recruitment.view')
        <a href="{{ route('admin.hr.recruitment.dashboard') }}" class="erp-btn-secondary mt-4 inline-block text-xs">{{ __('Open recruitment') }}</a>
    @endcan
</x-admin.card>
