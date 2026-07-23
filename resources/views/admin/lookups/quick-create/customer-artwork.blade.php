@php
    use App\Enums\CustomerArtworkType;
@endphp

<x-admin.lookup-nested-form :title="$title" :action="$action" enctype="multipart/form-data" max-width="2xl">
    @if ($customer)
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-800">{{ __('Customer') }}:</span>
            {{ $customer->company_name }}
        </p>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Select a customer in the parent form before uploading artwork.') }}
        </div>
    @endif

    <div>
        <label class="erp-label" for="artwork_name">{{ __('Artwork name') }}</label>
        <input
            type="text"
            id="artwork_name"
            name="artwork_name"
            class="erp-input w-full"
            value="{{ old('artwork_name') }}"
            maxlength="255"
            @required((bool) $customer)
            @disabled(! $customer)
        >
    </div>

    <div>
        <label class="erp-label" for="artwork_type">{{ __('Artwork type') }}</label>
        <select id="artwork_type" name="artwork_type" class="erp-input w-full" @required((bool) $customer) @disabled(! $customer)>
            @foreach ($artworkTypes as $type)
                <option value="{{ $type->value }}" @selected(old('artwork_type', CustomerArtworkType::Layout->value) === $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="erp-label" for="file">{{ __('Artwork file') }}</label>
        <input
            type="file"
            id="file"
            name="file"
            class="erp-input w-full"
            accept=".jpg,.jpeg,.png,.webp,.pdf"
            @required((bool) $customer)
            @disabled(! $customer)
        >
        <p class="mt-1 text-xs text-slate-500">{{ __('Accepted formats: JPG, PNG, WebP, PDF. Max 20 MB.') }}</p>
    </div>
</x-admin.lookup-nested-form>
