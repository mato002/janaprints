<x-admin-layout :title="__('Compose email')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Compose')]]">
    @include('admin.communications.email.partials.mailbox-chrome', [
        'mailbox' => $mailbox ?? ['sent' => 0, 'drafts' => 0, 'queued' => 0, 'needs_attention' => 0],
        'activeFolder' => 'compose',
        'filters' => [],
    ])
    <x-admin.page-header :title="__('Compose email')" />
    <form method="POST" action="{{ route('admin.communications.email.compose.store') }}" class="erp-card max-w-3xl space-y-4">
        @csrf
        <div><label class="text-sm font-medium">{{ __('To') }}</label><input type="text" name="to" class="erp-input w-full" value="{{ old('to', $to) }}" required placeholder="email@example.com"></div>
        <div><label class="text-sm font-medium">{{ __('CC') }}</label><input type="text" name="cc" class="erp-input w-full" value="{{ old('cc') }}"></div>
        <div><label class="text-sm font-medium">{{ __('BCC') }}</label><input type="text" name="bcc" class="erp-input w-full" value="{{ old('bcc') }}"></div>
        <div>
            <label class="text-sm font-medium">{{ __('Template') }}</label>
            <select name="communication_template_id" class="erp-input w-full">
                <option value="">{{ __('None') }}</option>
                @foreach ($templates as $tpl)
                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">{{ __('Subject') }}</label><input type="text" name="subject" class="erp-input w-full" value="{{ old('subject') }}" required></div>
        <div><label class="text-sm font-medium">{{ __('Body') }}</label><textarea name="body" rows="8" class="erp-input w-full" required>{{ old('body') }}</textarea></div>
        @if ($customer_id)<input type="hidden" name="customer_id" value="{{ $customer_id }}">@endif
        <div class="flex gap-2">
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Send') }}</button>
            <button type="submit" name="save_draft" value="1" class="erp-btn erp-btn--secondary">{{ __('Save draft') }}</button>
        </div>
    </form>
</x-admin-layout>
