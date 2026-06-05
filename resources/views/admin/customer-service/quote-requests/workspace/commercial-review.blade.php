@can('update', $quoteRequest)
    <section class="crm-360__card crm-360__card--form">
        <h2 class="crm-360__card-title">{{ __('Commercial Review') }}</h2>

        <form method="POST" action="{{ route('admin.public-quote-requests.update-review', $quoteRequest) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PATCH')

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    @foreach (App\Enums\PublicQuoteRequestStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($quoteRequest->status === $status)>{{ $status->workspaceLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Priority') }}</label>
                <select name="priority" class="erp-input w-full text-sm">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach (App\Enums\PublicQuoteRequestPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected($quoteRequest->priority === $priority)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Assigned Salesperson') }}</label>
                <select name="assigned_to" class="erp-input w-full text-sm">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($workspace['assignable_users'] as $user)
                        <option value="{{ $user->id }}" @selected($quoteRequest->assigned_to == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Expected Value') }}</label>
                <input type="number" step="0.01" min="0" name="expected_value" value="{{ old('expected_value', $quoteRequest->expected_value) }}" class="erp-input w-full text-sm" placeholder="0.00">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Probability %') }}</label>
                <input type="number" min="0" max="100" name="probability" value="{{ old('probability', $quoteRequest->probability) }}" class="erp-input w-full text-sm" placeholder="0">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Target Follow-up Date') }}</label>
                <input type="date" name="target_follow_up_at" value="{{ old('target_follow_up_at', $quoteRequest->target_follow_up_at?->format('Y-m-d')) }}" class="erp-input w-full text-sm">
            </div>

            <div class="sm:col-span-2 flex flex-wrap gap-2 pt-2">
                <button type="submit" class="crm-360__btn crm-360__btn--primary">{{ __('Save Review') }}</button>
                @if (Route::has('admin.quotations.create'))
                    <a href="{{ route('admin.quotations.create') }}" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">{{ __('Create Quotation') }}</a>
                @endif
                @if (Route::has('admin.crm.customers.create'))
                    <a href="{{ route('admin.crm.customers.create') }}" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">{{ __('Create Customer') }}</a>
                @endif
                @if (Route::has('admin.crm.leads.create'))
                    <a href="{{ route('admin.crm.leads.create') }}" class="crm-360__btn crm-360__btn--outline" data-turbo-frame="erp-main">{{ __('Convert To Lead') }}</a>
                @endif
            </div>
        </form>
    </section>
@endcan
