@can('communications.email.view')
    <div
        class="crm-360__tab-stack"
        x-data="{
            drawerOpen: false,
            loading: false,
            detail: null,
            async openDrawer(messageId) {
                this.drawerOpen = true;
                this.loading = true;
                this.detail = null;
                try {
                    const response = await fetch(`{{ url('admin/communications/email/messages') }}/${messageId}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.detail = data.message;
                    }
                } finally {
                    this.loading = false;
                }
            },
            closeDrawer() {
                this.drawerOpen = false;
                this.detail = null;
            },
        }"
    >
        <x-admin.card :padding="false" class="mb-4">
            <x-admin.index-toolbar :action="route('admin.crm.customers.show', $customer)" :reset-url="route('admin.crm.customers.show', $customer)" compact>
                <input type="hidden" name="tab" value="communications">
                <x-admin.filter-pill-select name="comm_type" :label="__('Type')" :selected="request('comm_type')">
                    <option value="">{{ __('All types') }}</option>
                    <option value="quotations" @selected(request('comm_type') === 'quotations')>{{ __('Quotations') }}</option>
                    <option value="invoices" @selected(request('comm_type') === 'invoices')>{{ __('Invoices') }}</option>
                    <option value="receipts" @selected(request('comm_type') === 'receipts')>{{ __('Receipts') }}</option>
                    <option value="general" @selected(request('comm_type') === 'general')>{{ __('General') }}</option>
                </x-admin.filter-pill-select>
            </x-admin.index-toolbar>
        </x-admin.card>

        <div class="erp-card overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Sender') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customerEmailMessages as $message)
                        <tr>
                            <td>{{ Str::limit($message['subject'], 50) }}</td>
                            <td class="text-xs">{{ $message['type_label'] }}</td>
                            <td class="text-xs">{{ $message['sender'] ?? '—' }}</td>
                            <td class="text-xs">{{ $message['date_formatted'] ?? '—' }}</td>
                            <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message['status_badge'] }}">{{ $message['status_label'] }}</span></td>
                            <td class="text-right">
                                <button type="button" class="text-sm text-erp-accent" @click="openDrawer({{ $message['id'] }})">{{ __('View') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No email communications for this customer yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.communications.email.partials.detail-drawer')
    </div>
@else
    <x-admin.empty-state icon="mail" :title="__('Email access required')" :description="__('You do not have permission to view customer email history.')" />
@endcan
