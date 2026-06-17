<x-admin-layout :title="__('Email employees')" :breadcrumbs="[['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => __('Email')]]">
    <x-admin.page-header
        :title="__('Email employees')"
        :description="__('Send a message from the HR mailbox to selected staff. Each employee receives their own email.')"
    />

    <form method="POST" action="{{ url()->route('admin.employees.email.send') }}" class="erp-card max-w-3xl space-y-4" data-turbo="false">
        @csrf

        @if ($allActive)
            <input type="hidden" name="all" value="1">
        @else
            @foreach ($recipients as $recipient)
                <input type="hidden" name="employees[]" value="{{ $recipient->id }}">
            @endforeach
        @endif

        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-sm font-medium text-slate-700">
                {{ trans_choice(':count recipient|:count recipients', $recipients->count(), ['count' => $recipients->count()]) }}
                @if ($allActive)
                    <span class="text-slate-500">({{ __('all active staff with email') }})</span>
                @endif
            </p>
            <ul class="mt-2 max-h-40 space-y-1 overflow-y-auto text-sm text-slate-600">
                @foreach ($recipients as $recipient)
                    <li>{{ $recipient->full_name }} &lt;{{ $recipient->email }}&gt;</li>
                @endforeach
            </ul>
        </div>

        <div>
            <label class="text-sm font-medium">{{ __('Subject') }}</label>
            <input type="text" name="subject" class="erp-input w-full" value="{{ old('subject', $subject) }}" required maxlength="255">
            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="text-sm font-medium">{{ __('Message') }}</label>
            <textarea name="body" rows="10" class="erp-input w-full" required>{{ old('body', $body) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">
                {{ __('Optional placeholders') }}: @{{ name }}, @{{ employee_number }}
            </p>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Send emails') }}</button>
            <a href="{{ url()->route('admin.employees.index') }}" class="erp-btn erp-btn--secondary" data-turbo="false">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
