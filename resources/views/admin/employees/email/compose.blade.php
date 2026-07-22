<x-admin.modal-form
    :title="__('Email employees')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Employees'), 'url' => route('admin.employees.index')],
        ['label' => __('Email')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="url()->route('admin.employees.email.send')">
        @if ($allActive)
            <input type="hidden" name="all" value="1">
        @else
            @foreach ($recipients as $recipient)
                <input type="hidden" name="employees[]" value="{{ $recipient->id }}">
            @endforeach
        @endif

        <p class="mb-4 text-sm text-slate-500">
            {{ __('Send a message from the HR mailbox to selected staff. Each employee receives their own email.') }}
        </p>

        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
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

        <div class="space-y-4">
            <div>
                <label class="erp-label" for="email_subject">{{ __('Subject') }}</label>
                <input id="email_subject" type="text" name="subject" class="erp-input w-full" value="{{ old('subject', $subject) }}" required maxlength="255">
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="erp-label" for="email_body">{{ __('Message') }}</label>
                <textarea id="email_body" name="body" rows="10" class="erp-input w-full" required>{{ old('body', $body) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Optional placeholders') }}: @{{ name }}, @{{ employee_number }}
                </p>
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Send emails') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
