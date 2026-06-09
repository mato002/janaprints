@if (session('status'))
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-erp-success" role="status" data-erp-flash-status>
        <x-admin.icon name="badge-check" class="w-5 h-5 shrink-0" />
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-erp-danger" role="alert">
        <p class="font-medium">{{ __('Please fix the following:') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
