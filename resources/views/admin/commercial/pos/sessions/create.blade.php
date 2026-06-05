<x-admin-layout :title="__('Open POS Session')">
    <x-admin.page-header :title="__('Open POS Session')" :description="__('Start a cashier session with opening float and cash count.')" />

    @if ($activeSession)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ __('You already have an active session :number.', ['number' => $activeSession->session_number]) }}
            <a href="{{ route('admin.commercial.pos.sessions.show', $activeSession) }}" class="font-semibold underline">{{ __('View session') }}</a>
        </div>
    @endif

    <x-admin.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.commercial.pos.sessions.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-[11px] text-slate-500" for="cashier_id">{{ __('Cashier') }}</label>
                <select id="cashier_id" name="cashier_id" class="erp-input mt-1 w-full" required>
                    @foreach ($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" @selected(old('cashier_id', $defaultCashierId) == $cashier->id)>{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="opening_float">{{ __('Session Float') }}</label>
                <input type="number" step="0.01" min="0" id="opening_float" name="opening_float" value="{{ old('opening_float', 0) }}" class="erp-input mt-1 w-full" required>
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="opening_cash">{{ __('Opening Cash') }}</label>
                <input type="number" step="0.01" min="0" id="opening_cash" name="opening_cash" value="{{ old('opening_cash', 0) }}" class="erp-input mt-1 w-full" required>
            </div>
            <div>
                <label class="text-[11px] text-slate-500" for="opening_notes">{{ __('Notes') }}</label>
                <textarea id="opening_notes" name="opening_notes" rows="3" class="erp-input mt-1 w-full">{{ old('opening_notes') }}</textarea>
            </div>
            <button type="submit" class="erp-btn-primary" @if ($activeSession) disabled @endif>{{ __('Open session') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
