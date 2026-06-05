@php
    $media = $workspaceData['media_library'] ?? ['images' => 0, 'files' => 0, 'items' => collect(), 'by_month' => collect()];
    $photos = collect($media['items'] ?? [])->filter(fn ($i) => $i['is_image']);
    $docs = collect($media['items'] ?? [])->reject(fn ($i) => $i['is_image']);
    $photosByMonth = $photos->groupBy('month_key');
@endphp

<div class="text-xs" x-data="{ mediaTab: 'photos' }">
    <div class="mb-2 flex gap-1 border-b border-erp-border">
        <button type="button" @click="mediaTab='photos'" class="px-2 py-1.5 font-semibold uppercase text-[9px]" :class="mediaTab==='photos' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-500'">
            {{ __('Photos') }} ({{ $media['images'] }})
        </button>
        <button type="button" @click="mediaTab='files'" class="px-2 py-1.5 font-semibold uppercase text-[9px]" :class="mediaTab==='files' ? 'border-b-2 border-erp-accent text-erp-accent' : 'text-slate-500'">
            {{ __('Files') }} ({{ $media['files'] }})
        </button>
    </div>

    <p class="mb-2 text-[11px] text-slate-500">{{ __('Grouped by date — tap to jump to that message in the chat.') }}</p>

    @can('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)
        <form method="POST" action="{{ route('admin.communications.inbox.attachments.store', $active) }}" enctype="multipart/form-data" class="mb-3 flex gap-1" data-turbo-frame="erp-main">
            @csrf
            <input type="file" name="file" class="erp-input min-w-0 flex-1 text-[10px]" accept="image/*,.pdf">
            <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs shrink-0">{{ __('Upload') }}</button>
        </form>
    @endcan

    <div x-show="mediaTab==='photos'" x-cloak class="max-h-[50vh] space-y-3 overflow-y-auto">
        @forelse ($photosByMonth as $monthKey => $monthItems)
            <div>
                <p class="mb-1 font-semibold text-slate-600">{{ $monthItems->first()['month_label'] ?? $monthKey }}</p>
                <div class="grid grid-cols-3 gap-1">
                    @foreach ($monthItems as $item)
                        <button
                            type="button"
                            class="relative aspect-square overflow-hidden rounded-md bg-slate-100"
                            @click="$dispatch('open-chat-item', '{{ $item['dom_id'] }}')"
                            title="{{ $item['at']->format('d M Y H:i') }}"
                        >
                            <img src="{{ $item['file_url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            <span class="absolute bottom-0 left-0 right-0 bg-black/50 px-1 py-0.5 text-[9px] text-white">{{ $item['at']->format('d M') }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No photos sent yet.') }}</p>
        @endforelse
    </div>

    <div x-show="mediaTab==='files'" x-cloak class="max-h-[50vh] space-y-2 overflow-y-auto">
        @forelse ($docs as $item)
            <div class="rounded-lg border border-erp-border bg-white p-2">
                <p class="font-medium text-erp-primary">{{ $item['label'] }}</p>
                <p class="text-slate-400">{{ $item['at']->format('d M Y, H:i') }}</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    <x-admin.crm-btn type="button" variant="ghost" size="xs" @click="$dispatch('open-chat-item', '{{ $item['dom_id'] }}')">{{ __('View in chat') }}</x-admin.crm-btn>
                    <x-admin.crm-btn variant="outline" size="xs" :href="$item['download_url']">{{ __('Download') }}</x-admin.crm-btn>
                    @can('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)
                        <form method="POST" action="{{ route('admin.communications.inbox.attachments.destroy', [$active, $item['id']]) }}" class="inline" data-turbo-frame="erp-main" onsubmit="return confirm(@js(__('Remove this file?')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('No documents yet.') }}</p>
        @endforelse
    </div>
</div>
