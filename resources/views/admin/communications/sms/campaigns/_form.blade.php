@php
    $campaign = $campaign ?? null;
@endphp

<div x-data="smsCampaignForm(@js(['previewUrl' => route('admin.communications.sms.campaigns.preview')]))" class="space-y-4">
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="space-y-3">
            <div>
                <label class="erp-label">{{ __('Campaign name') }}</label>
                <input type="text" name="name" class="erp-input w-full" value="{{ old('name', $campaign?->name) }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Description') }}</label>
                <textarea name="description" class="erp-input w-full" rows="2">{{ old('description', $campaign?->description) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
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
                <label class="erp-label">{{ __('Message template') }}</label>
                <textarea name="message_template" class="erp-input w-full font-mono text-sm" rows="5" x-model="messageTemplate" required>{{ old('message_template', $campaign?->message_template) }}</textarea>
            </div>
            <div>
                <label class="erp-label">{{ __('Recipient source') }}</label>
                <select name="recipient_source" class="erp-input w-full">
                    @foreach ($sources as $source)
                        <option value="{{ $source->value }}" @selected(old('recipient_source', $campaign?->recipient_source?->value) === $source->value)>{{ $source->label() }}</option>
                    @endforeach
                </select>
            </div>
            @include('admin.communications.sms.campaigns._filters')
            <div>
                <label class="erp-label">{{ __('Manual numbers') }} <span class="text-slate-400">({{ __('one per line') }})</span></label>
                <textarea name="manual_phones" class="erp-input w-full font-mono text-sm" rows="3" placeholder="+254712345678">{{ old('manual_phones') }}</textarea>
            </div>
        </div>
        <div class="erp-card h-fit">
            <h2 class="erp-card-title">{{ __('Message preview') }}</h2>
            <p class="text-xs text-slate-500 mb-2">{{ __('Rendered via COM-1 template engine') }}</p>
            <button type="button" class="erp-btn erp-btn--secondary erp-btn--sm w-full" @click="runPreview()">{{ __('Render preview') }}</button>
            <template x-if="preview">
                <div class="mt-3 space-y-2 text-sm">
                    <pre class="whitespace-pre-wrap rounded border border-emerald-200 bg-emerald-50 p-2" x-text="preview.body"></pre>
                    <p class="text-xs text-slate-600">{{ __('Characters') }}: <span x-text="preview.character_count"></span> · {{ __('Segments') }}: <span x-text="preview.segments"></span></p>
                </div>
            </template>
        </div>
    </div>
</div>
