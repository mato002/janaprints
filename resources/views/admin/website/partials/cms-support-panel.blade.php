@php
    $checklist = $checklist ?? app(\App\Support\Website\WebsiteContentWorkspacePresenter::class)->deploymentChecklist();
@endphp

<div
    x-data="{ guideOpen: false, checklistOpen: false }"
    class="mb-4 flex flex-wrap items-center gap-2"
>
    <button type="button" class="erp-btn-secondary text-xs" @click="guideOpen = !guideOpen">
        {{ __('Website CMS Guide') }}
    </button>
    <button type="button" class="erp-btn-secondary text-xs" @click="checklistOpen = !checklistOpen">
        {{ __('Deployment Checklist') }}
    </button>

    <div
        x-show="guideOpen"
        x-cloak
        class="w-full rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700"
    >
        <h3 class="mb-2 font-semibold text-slate-900">{{ __('Quick start for non-technical staff') }}</h3>
        <ol class="list-decimal space-y-2 pl-5">
            <li>{{ __('Replace a homepage image: Media Library → filter Hero → Upload/Replace → save alt text.') }}</li>
            <li>{{ __('Update footer contact: Footer & Contact Settings → Contact tab → save phone, email, and address.') }}</li>
            <li>{{ __('Add a gallery project: Gallery → Add Gallery Item → upload image, title, and category.') }}</li>
            <li>{{ __('Publish gallery work: check Published on public site (requires publish permission).') }}</li>
            <li>{{ __('Restore a fallback image: Media Library → Reset on the slot, or Remove Uploaded Image on the edit form.') }}</li>
        </ol>
    </div>

    <div
        x-show="checklistOpen"
        x-cloak
        class="w-full rounded-lg border border-slate-200 bg-white p-4 text-sm"
    >
        <h3 class="mb-3 font-semibold text-slate-900">{{ __('Deployment readiness (read-only)') }}</h3>
        <ul class="space-y-2">
            @foreach ($checklist as $checklistItem)
                <li class="flex items-start gap-2">
                    @if ($checklistItem['ready'])
                        <x-admin.status-badge variant="success">{{ __('Ready') }}</x-admin.status-badge>
                    @else
                        <x-admin.status-badge variant="neutral">{{ __('Pending') }}</x-admin.status-badge>
                    @endif
                    <div>
                        <p class="font-medium text-slate-900">{{ $checklistItem['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $checklistItem['detail'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
