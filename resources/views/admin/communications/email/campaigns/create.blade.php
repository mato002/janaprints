<x-admin-layout :title="__('New email campaign')">
    @include('admin.communications.email.partials.nav')
    <form method="POST" action="{{ route('admin.communications.email.campaigns.store') }}" class="erp-card max-w-3xl space-y-4">
        @csrf
        <div><label class="text-sm font-medium">{{ __('Name') }}</label><input name="name" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium">{{ __('Type') }}</label>
            <select name="campaign_type" class="erp-input w-full">
                @foreach (\App\Enums\EmailCampaignType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">{{ __('To (comma-separated)') }}</label><input name="to" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium">{{ __('Template') }}</label>
            <select name="communication_template_id" class="erp-input w-full"><option value="">{{ __('None') }}</option>
                @foreach ($templates as $tpl)<option value="{{ $tpl->id }}">{{ $tpl->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">{{ __('Subject') }}</label><input name="subject" class="erp-input w-full" required></div>
        <div><label class="text-sm font-medium">{{ __('Body') }}</label><textarea name="body" rows="6" class="erp-input w-full" required></textarea></div>
        <div><label class="text-sm font-medium">{{ __('Schedule at') }}</label><input type="datetime-local" name="scheduled_at" class="erp-input w-full"></div>
        <button type="submit" class="erp-btn erp-btn--primary">{{ __('Save campaign') }}</button>
    </form>
</x-admin-layout>
