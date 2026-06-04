<div class="border-t border-erp-border bg-slate-50 px-4 py-2">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-xs font-semibold uppercase text-slate-600">{{ __('Attachments') }}</h3>
        @can('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)
            <form method="POST" action="{{ route('admin.communications.inbox.attachments.store', $active) }}" enctype="multipart/form-data" class="flex gap-1">
                @csrf
                <select name="attachment_type" class="erp-input text-xs">
                    <option value="image">{{ __('Image') }}</option>
                    <option value="pdf">{{ __('PDF') }}</option>
                    <option value="artwork">{{ __('Artwork') }}</option>
                    <option value="quotation">{{ __('Quotation') }}</option>
                    <option value="invoice">{{ __('Invoice') }}</option>
                    <option value="proof">{{ __('Proof') }}</option>
                </select>
                <input type="file" name="file" class="text-xs max-w-[8rem]">
                <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs">{{ __('Upload') }}</button>
            </form>
        @endcan
    </div>
    @if ($active->attachments->isNotEmpty())
        <ul class="mt-2 flex flex-wrap gap-2 text-xs">
            @foreach ($active->attachments as $att)
                <li class="rounded border border-erp-border bg-white px-2 py-1 flex items-center gap-2">
                    <span>{{ $att->label ?? __('File') }}</span>
                    <span class="text-slate-400">{{ $att->attachment_type }}</span>
                    @if ($att->file_path)
                        <a href="{{ route('admin.communications.inbox.attachments.download', [$active, $att]) }}" class="text-erp-accent hover:underline">{{ __('Download') }}</a>
                        @if (str_starts_with((string) $att->attachment_type, 'image') || str_contains($att->file_path, '.jpg') || str_contains($att->file_path, '.png'))
                            <a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" class="text-erp-accent">{{ __('Preview') }}</a>
                        @endif
                    @endif
                    @if ($att->attachable_type)
                        <span class="text-slate-500">{{ __('Linked record') }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
