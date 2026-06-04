<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('RFQ Response') }} — {{ $rfq->rfq_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-2xl font-semibold">{{ __('Request For Quotation') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $rfq->rfq_number }} · {{ $rfqVendor->vendor->vendor_name }}</p>
        @if (session('status'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('rfq.portal.submit', $rfqVendor->response_token) }}" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-xl bg-white p-6 shadow-sm">
            @csrf
            @foreach ($rfq->items as $index => $item)
                <div class="border-b border-slate-100 pb-4">
                    <p class="font-medium">{{ $item->description }}</p>
                    <p class="text-sm text-slate-500">{{ __('Qty') }}: {{ $item->quantity }}</p>
                    <input type="hidden" name="lines[{{ $index }}][rfq_item_id]" value="{{ $item->id }}">
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <input type="number" step="0.01" name="lines[{{ $index }}][quoted_price]" class="erp-input w-full" placeholder="{{ __('Unit price') }}" required>
                        <input type="number" name="lines[{{ $index }}][lead_time_days]" class="erp-input w-full" placeholder="{{ __('Lead time (days)') }}">
                    </div>
                    <input type="text" name="lines[{{ $index }}][warranty]" class="erp-input mt-2 w-full" placeholder="{{ __('Warranty') }}">
                    <input type="text" name="lines[{{ $index }}][delivery_terms]" class="erp-input mt-2 w-full" placeholder="{{ __('Delivery terms') }}">
                </div>
            @endforeach
            <div>
                <label class="text-sm font-medium">{{ __('Attachment (optional)') }}</label>
                <input type="file" name="attachment" class="mt-1 block w-full text-sm">
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Submit quotation') }}</button>
        </form>
    </div>
</body>
</html>
