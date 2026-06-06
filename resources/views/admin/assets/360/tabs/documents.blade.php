<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <x-admin.card>
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold">{{ __('Uploaded Documents') }}</h3>
            <a href="{{ route('admin.assets.documents.index', $asset) }}" class="erp-link text-sm">{{ __('Manage') }}</a>
        </div>
        <ul class="space-y-2 text-sm">
            @forelse ($tabData['uploaded_documents'] as $doc)
                <li>
                    <a href="{{ route('admin.assets.documents.download', $doc) }}" class="erp-link">{{ $doc->title }}</a>
                    <span class="text-slate-500">— {{ $doc->document_type->label() }}</span>
                </li>
            @empty
                <li class="text-slate-500">{{ __('No uploaded documents.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Procurement & Workflow Links') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($tabData['procurement_documents'] as $doc)
                <li>{{ $doc->document_label ?? class_basename($doc->document_type) }} (#{{ $doc->document_id }})</li>
            @empty
                <li class="text-slate-500">{{ __('No linked procurement documents.') }}</li>
            @endforelse
            @foreach ($tabData['handovers'] as $h)
                <li>{{ __('Handover') }} {{ $h->handover_no }}</li>
            @endforeach
            @foreach ($tabData['transfers'] as $t)
                <li>{{ __('Transfer') }} {{ $t->transfer_no }}</li>
            @endforeach
        </ul>
    </x-admin.card>
</div>
