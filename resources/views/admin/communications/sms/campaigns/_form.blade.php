@php
    $campaign = $campaign ?? null;
@endphp

<div
    x-data="smsCampaignForm(@js([
        'previewUrl' => route('admin.communications.sms.campaigns.preview'),
        'estimateUrl' => route('admin.communications.sms.campaigns.estimate-recipients'),
        'sendMode' => old('send_mode', $campaign?->send_mode?->value ?? 'immediate'),
        'recipientSource' => old('recipient_source', $campaign?->recipient_source?->value ?? 'customers'),
        'messageTemplate' => old('message_template', $campaign?->message_template ?? ''),
        'manualPhones' => old('manual_phones', ''),
        'pickerOptions' => $pickerOptions ?? [],
        'selectedRecipientIds' => collect(old('recipient_filters.ids', $campaign?->recipient_filters['ids'] ?? []))->map(fn ($id) => (string) $id)->values()->all(),
        'filters' => [
            'branch_id' => (string) old('recipient_filters.branch_id', $campaign?->recipient_filters['branch_id'] ?? ''),
            'customer_type' => (string) old('recipient_filters.customer_type', $campaign?->recipient_filters['customer_type'] ?? ''),
            'status' => (string) old('recipient_filters.status', $campaign?->recipient_filters['status'] ?? ''),
            'has_outstanding' => (string) old('recipient_filters.has_outstanding', $campaign?->recipient_filters['has_outstanding'] ?? ''),
            'department_id' => (string) old('recipient_filters.department_id', $campaign?->recipient_filters['department_id'] ?? ''),
            'employment_status' => (string) old('recipient_filters.employment_status', $campaign?->recipient_filters['employment_status'] ?? ''),
            'vendor_type' => (string) old('recipient_filters.vendor_type', $campaign?->recipient_filters['vendor_type'] ?? ''),
        ],
    ]))"
    class="grid gap-3 lg:grid-cols-12"
>
    {{-- Campaign basics --}}
    <div class="lg:col-span-8 grid gap-3 sm:grid-cols-2">
        @if ($campaign)
            <div class="sm:col-span-2">
                <label class="erp-label">{{ __('Campaign name') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <input type="text" name="name" class="erp-input w-full" value="{{ old('name', $campaign->name) }}" placeholder="{{ __('Leave blank to keep the current name') }}">
            </div>
        @endif
        <div class="grid gap-2" :class="sendMode === 'scheduled' ? 'grid-cols-2' : 'grid-cols-1'">
            <div>
                <label class="erp-label">{{ __('Send mode') }}</label>
                <select name="send_mode" class="erp-input w-full" x-model="sendMode">
                    @foreach ($sendModes as $mode)
                        <option value="{{ $mode->value }}" @selected(old('send_mode', $campaign?->send_mode?->value ?? 'immediate') === $mode->value)>{{ $mode->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="sendMode === 'scheduled'" x-cloak>
                <label class="erp-label">{{ __('Scheduled at') }}</label>
                <input type="datetime-local" name="scheduled_at" class="erp-input w-full" value="{{ old('scheduled_at', $campaign?->scheduled_at?->format('Y-m-d\TH:i')) }}">
            </div>
        </div>

        <div>
            <label class="erp-label">{{ __('COM-1 template') }}</label>
            <select name="communication_template_id" class="erp-input w-full" @change="onTemplateChange($event)">
                <option value="">{{ __('Custom message') }}</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" data-body="{{ e($template->body) }}" @selected(old('communication_template_id', $campaign?->communication_template_id) == $template->id)>{{ $template->name }} ({{ $template->code }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label">{{ __('Recipient source') }}</label>
            <select name="recipient_source" class="erp-input w-full" x-model="recipientSource" @change="onRecipientSourceChange()">
                @foreach ($sources as $source)
                    <option value="{{ $source->value }}" @selected(old('recipient_source', $campaign?->recipient_source?->value) === $source->value)>{{ $source->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="erp-label">{{ __('Message template') }}</label>
            <textarea name="message_template" class="erp-input w-full font-mono text-sm" rows="4" x-model="messageTemplate" required>{{ old('message_template', $campaign?->message_template) }}</textarea>
        </div>
    </div>

    {{-- Preview --}}
    <aside class="lg:col-span-4 lg:sticky lg:top-0 h-fit rounded-lg border border-erp-border bg-slate-50/80 p-3 space-y-2">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Message preview') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Rendered via COM-1 template engine') }}</p>
        </div>
        <button type="button" class="erp-btn erp-btn--secondary erp-btn--sm w-full" @click="runPreview()">{{ __('Render preview') }}</button>
        <template x-if="preview">
            <div class="space-y-2 text-sm">
                <pre class="whitespace-pre-wrap rounded border border-emerald-200 bg-emerald-50 p-2 text-xs" x-text="preview.body"></pre>
                <p class="text-xs text-slate-600">
                    {{ __('Characters') }}: <span x-text="preview.character_count"></span>
                    · {{ __('Segments') }}: <span x-text="preview.segments"></span>
                </p>
            </div>
        </template>
        <p x-show="!preview" class="rounded border border-dashed border-erp-border bg-white px-3 py-6 text-center text-xs text-slate-500">
            {{ __('Click render to see character count and segments.') }}
        </p>
        <p class="rounded bg-white border border-erp-border px-2 py-1.5 text-xs text-slate-600" x-show="audienceEstimate !== null" x-cloak>
            {{ __('Audience') }}:
            <span class="font-semibold text-erp-primary" x-text="audienceEstimate"></span>
        </p>
    </aside>

    {{-- Recipients: picker (when applicable) + filters --}}
    <div
        class="lg:col-span-12 grid gap-3"
        :class="recipientSource === 'dynamic' ? 'lg:grid-cols-1' : 'lg:grid-cols-2'"
        x-show="['customers', 'dynamic', 'leads', 'employees', 'suppliers'].includes(recipientSource)"
        x-cloak
    >
        @include('admin.communications.sms.campaigns._picker')
        @include('admin.communications.sms.campaigns._filters')
    </div>

    <div class="lg:col-span-12">
        @include('admin.communications.sms.campaigns._import')
    </div>
</div>
