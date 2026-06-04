<h3 class="mt-4 font-medium">{{ __('Journal lines') }}</h3>
<p class="mb-2 text-[11px] text-slate-500">{{ __('Total debits must equal total credits. Each line is debit OR credit.') }}</p>
<div class="space-y-2">
    <div class="grid grid-cols-12 gap-2 text-[11px] font-medium uppercase text-slate-400">
        <span class="col-span-5">{{ __('Account') }}</span>
        <span class="col-span-2">{{ __('Debit') }}</span>
        <span class="col-span-2">{{ __('Credit') }}</span>
        <span class="col-span-3">{{ __('Line note') }}</span>
    </div>
    @php $lineCount = max(4, count($journal?->lines ?? [])); @endphp
    @for ($i = 0; $i < $lineCount; $i++)
        @php
            $line = $journal?->lines?->get($i);
            $oldLine = old('lines.'.$i, []);
        @endphp
        <div class="grid grid-cols-12 gap-2">
            <select name="lines[{{ $i }}][gl_account_id]" class="erp-input col-span-5" required>
                <option value="">{{ __('— Account —') }}</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected(($oldLine['gl_account_id'] ?? $line?->gl_account_id) == $account->id)>
                        {{ $account->code }} — {{ $account->name }}
                    </option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0" name="lines[{{ $i }}][debit]" class="erp-input col-span-2" value="{{ $oldLine['debit'] ?? $line?->debit ?? '' }}" placeholder="0.00">
            <input type="number" step="0.01" min="0" name="lines[{{ $i }}][credit]" class="erp-input col-span-2" value="{{ $oldLine['credit'] ?? $line?->credit ?? '' }}" placeholder="0.00">
            <input type="text" name="lines[{{ $i }}][description]" class="erp-input col-span-3" value="{{ $oldLine['description'] ?? $line?->description ?? '' }}" placeholder="{{ __('Optional') }}">
        </div>
    @endfor
</div>
