@can('update', $quoteRequest)
    <x-admin.record-workspace.section
        id="qr-360-review"
        :title="__('Sales review')"
        tone="edit"
    >
        <form method="POST" action="{{ route('admin.public-quote-requests.update-review', $quoteRequest) }}" class="qr-360__review-grid">
            @csrf
            @method('PATCH')

            <div>
                <label class="qr-360__label">{{ __('Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    @foreach (App\Enums\PublicQuoteRequestStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected($quoteRequest->status === $status)>{{ $status->workspaceLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="qr-360__label">{{ __('Priority') }}</label>
                <select name="priority" class="erp-input w-full text-sm">
                    <option value="">{{ __('Not set') }}</option>
                    @foreach (App\Enums\PublicQuoteRequestPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected($quoteRequest->priority === $priority)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="qr-360__label">{{ __('Assigned salesperson') }}</label>
                <select name="assigned_to" class="erp-input w-full text-sm">
                    <option value="">{{ __('Unassigned') }}</option>
                    @foreach ($workspace['assignable_users'] as $user)
                        <option value="{{ $user->id }}" @selected($quoteRequest->assigned_to == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="qr-360__label">{{ __('Expected value') }}</label>
                <input type="number" step="0.01" min="0" name="expected_value" value="{{ old('expected_value', $quoteRequest->expected_value) }}" class="erp-input w-full text-sm" placeholder="0.00">
            </div>

            <div>
                <label class="qr-360__label">{{ __('Follow-up date') }}</label>
                <input type="date" name="target_follow_up_at" value="{{ old('target_follow_up_at', $quoteRequest->target_follow_up_at?->format('Y-m-d')) }}" class="erp-input w-full text-sm">
            </div>

            <div>
                <label class="qr-360__label">{{ __('Probability %') }}</label>
                <input type="number" min="0" max="100" name="probability" value="{{ old('probability', $quoteRequest->probability) }}" class="erp-input w-full text-sm" placeholder="0">
            </div>

            <div class="qr-360__review-actions">
                <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm">{{ __('Save review') }}</button>
            </div>
        </form>
    </x-admin.record-workspace.section>
@endcan
