<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Customer Files') }}</h2>
        <ul class="crm-360__file-list" role="list">
            @forelse ($customer->files as $file)
                <li class="crm-360__file-row">
                    <div>
                        <span class="font-medium text-erp-primary">{{ $file->original_name }}</span>
                        <span class="block text-[11px] text-slate-500">{{ $file->uploader?->name }} · {{ $file->created_at?->diffForHumans() }}</span>
                    </div>
                    @can('update', $customer)
                        <form method="POST" action="{{ route('admin.crm.customers.files.destroy', [$customer, $file]) }}">@csrf @method('DELETE')
                            <x-admin.crm-btn type="submit" variant="danger" size="sm">{{ __('Remove') }}</x-admin.crm-btn>
                        </form>
                    @endcan
                </li>
            @empty
                <li class="crm-360__empty-inline">{{ __('No customer files uploaded') }}</li>
            @endforelse
        </ul>
        @can('update', $customer)
            <form method="POST" action="{{ route('admin.crm.customers.files.store', $customer) }}" enctype="multipart/form-data" data-turbo-frame="erp-main" class="crm-360__upload-form mt-4">
                @csrf
                <label class="erp-label">{{ __('Upload file') }}</label>
                <input type="file" name="file" class="erp-input text-sm" required>
                <div class="mt-3">
                    <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Upload') }}</x-admin.crm-btn>
                </div>
            </form>
        @endcan
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Artwork Files') }}</h2>
        @if ($canArtwork && $commercial['artwork']->isNotEmpty())
            <ul class="crm-360__mini-list" role="list">
                @foreach ($commercial['artwork'] as $row)
                    <li>
                        @if ($row['url'])
                            <a href="{{ $row['url'] }}" class="crm-360__row-link" data-turbo-frame="erp-main">{{ $row['number'] }}</a>
                        @else
                            {{ $row['number'] }}
                        @endif
                        <span class="block text-[11px] text-slate-500">{{ $row['status'] }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="crm-360__empty-inline">{{ __('No artwork requests linked') }}</p>
        @endif
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Documents') }}</h2>
        <p class="text-[11px] text-slate-500">{{ __('General documents are stored as customer files above.') }}</p>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Approvals') }}</h2>
        <p class="crm-360__empty-inline">{{ __('Approval documents appear when artwork workflows are connected.') }}</p>
    </section>
</div>
