@php
    $topupCfg = $topupConfig ?? ['enabled' => false, 'currency' => 'KES', 'min_amount' => 10, 'max_amount' => 50000];
    $pollReference = session('sms_topup_poll_reference');
    $openDefault = filled($pollReference)
        || ($errors->has('sms_topup') ?? false)
        || ($errors->has('amount') ?? false)
        || ($errors->has('phone') ?? false);
@endphp

<div
    x-data="smsCrmTopupModal({
        open: {{ $openDefault ? 'true' : 'false' }},
        polling: {{ filled($pollReference) ? 'true' : 'false' }},
        reference: @js($pollReference),
        statusUrlTemplate: @js(route('admin.communications.sms.credits.topup.status', ['reference' => '__REF__'], absolute: false)),
        topupUrl: @js(route('admin.communications.sms.credits.topup', absolute: false)),
        enabled: @js((bool) ($topupCfg['enabled'] ?? false)),
        currency: @js($topupCfg['currency'] ?? 'KES'),
        minAmount: @js((float) ($topupCfg['min_amount'] ?? 10)),
        maxAmount: @js((float) ($topupCfg['max_amount'] ?? 50000)),
    })"
    @open-sms-crm-topup.window="open()"
>
    <div
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-[70] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="sms-crm-topup-title"
    >
        <div class="fixed inset-0 bg-erp-primary/50 backdrop-blur-[1px]" @click="close()"></div>
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-xl border border-erp-border bg-white shadow-2xl" @click.stop>
            <div class="flex items-center justify-between gap-3 border-b border-erp-border px-5 py-4">
                <div>
                    <h2 id="sms-crm-topup-title" class="text-lg font-semibold text-erp-primary">{{ __('Top up SMS credits') }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('Pay with M-Pesa via Pradytec CRM — real wallet credits, not a local adjustment.') }}</p>
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-40" @click="close()" :disabled="busy" aria-label="{{ __('Close') }}">
                    <x-admin.icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <div class="space-y-4 px-5 py-4">
                <template x-if="formError">
                    <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" x-text="formError"></div>
                </template>

                {{-- Waiting / result panel --}}
                <div x-show="phase !== 'form'" x-cloak class="space-y-3">
                    <div
                        class="rounded-lg border px-3 py-3 text-sm"
                        :class="{
                            'border-sky-200 bg-sky-50 text-sky-900': phase === 'waiting' || phase === 'submitting',
                            'border-emerald-200 bg-emerald-50 text-emerald-900': phase === 'completed',
                            'border-red-200 bg-red-50 text-red-900': phase === 'failed',
                        }"
                        role="status"
                        aria-live="polite"
                    >
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5" x-show="phase === 'waiting' || phase === 'submitting'">
                                <svg class="h-5 w-5 animate-spin text-sky-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium" x-text="statusTitle"></p>
                                <p class="mt-1 text-xs opacity-90" x-text="statusMessage"></p>
                                <p class="mt-2 text-xs opacity-80" x-show="phase === 'waiting'">
                                    {{ __('Enter your M-Pesa PIN on your phone. This window stays open until payment is confirmed or fails.') }}
                                </p>
                                <dl class="mt-3 grid grid-cols-2 gap-2 text-xs" x-show="amount || phone">
                                    <div>
                                        <dt class="opacity-70">{{ __('Amount') }}</dt>
                                        <dd class="font-semibold tabular-nums" x-text="amountLabel"></dd>
                                    </div>
                                    <div>
                                        <dt class="opacity-70">{{ __('Phone') }}</dt>
                                        <dd class="font-semibold" x-text="phone"></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-erp-border pt-4" x-show="phase === 'completed' || phase === 'failed'">
                        <button type="button" class="erp-btn-secondary" @click="closeAndRefresh()" x-show="phase === 'completed'">{{ __('Done') }}</button>
                        <button type="button" class="erp-btn-secondary" @click="resetToForm()" x-show="phase === 'failed'">{{ __('Try again') }}</button>
                        <button type="button" class="erp-btn-primary" @click="closeAndRefresh()" x-show="phase === 'failed'">{{ __('Close') }}</button>
                    </div>
                </div>

                {{-- Input form --}}
                <div x-show="phase === 'form'" x-cloak>
                    @if ($topupCfg['enabled'] ?? false)
                        <form class="space-y-3" @submit.prevent="submitTopup()">
                            <div>
                                <label class="erp-label" for="sms-crm-topup-phone">{{ __('M-Pesa phone number') }}</label>
                                <input
                                    id="sms-crm-topup-phone"
                                    type="tel"
                                    class="erp-input w-full"
                                    x-model="phone"
                                    placeholder="07XXXXXXXX"
                                    required
                                    :disabled="busy"
                                >
                            </div>
                            <div>
                                <label class="erp-label" for="sms-crm-topup-amount">{{ __('Amount') }} ({{ $topupCfg['currency'] ?? 'KES' }})</label>
                                <input
                                    id="sms-crm-topup-amount"
                                    type="number"
                                    class="erp-input w-full"
                                    x-model="amount"
                                    min="{{ (int) ($topupCfg['min_amount'] ?? 10) }}"
                                    max="{{ (int) ($topupCfg['max_amount'] ?? 50000) }}"
                                    step="1"
                                    required
                                    :disabled="busy"
                                >
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __(':min–:max :currency via Pradytec paybill STK push.', [
                                        'min' => number_format((float) ($topupCfg['min_amount'] ?? 10), 0),
                                        'max' => number_format((float) ($topupCfg['max_amount'] ?? 50000), 0),
                                        'currency' => $topupCfg['currency'] ?? 'KES',
                                    ]) }}
                                </p>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-erp-border pt-4">
                                <button type="button" class="erp-btn-secondary" @click="close()" :disabled="busy">{{ __('Cancel') }}</button>
                                <button type="submit" class="erp-btn-primary" :disabled="busy">
                                    <span x-show="!busy">{{ __('Send M-Pesa prompt') }}</span>
                                    <span x-show="busy" x-cloak>{{ __('Sending prompt…') }}</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-900">
                            {{ $topupCfg['error'] ?? __('Configure PRADYTEC_SMS_API_KEY and PRADYTEC_SMS_CLIENT_ID in .env to enable CRM top-ups.') }}
                        </div>
                        <div class="flex justify-end border-t border-erp-border pt-4">
                            <button type="button" class="erp-btn-secondary" @click="close()">{{ __('Close') }}</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
