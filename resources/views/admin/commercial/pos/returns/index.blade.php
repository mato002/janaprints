<x-admin-layout :title="__('Return History')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Returns'), 'url' => route('admin.commercial.pos.returns.dashboard')], ['label' => __('History')]]">
    <x-admin.page-header :title="__('Return History')" :description="__('All POS return transactions and reversal trail.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosReturn::class)
                <a href="{{ \App\Support\Navigation\WorkspaceEmbed::url(route('admin.commercial.pos.returns.create')) }}" data-turbo-frame="{{ \App\Support\Navigation\WorkspaceEmbed::turboFrame() }}" data-turbo-action="advance" class="erp-btn-primary">{{ __('New return') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="return_type" class="erp-toolbar-select" aria-label="{{ __('Return type') }}">
                <option value="">{{ __('All types') }}</option>
                @foreach ($returnTypes as $type)
                    <option value="{{ $type->value }}" @selected(($filters['return_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('Date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search returns…')"
        export-filename="pos-returns"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Return #') }}</th>
                <th scope="col">{{ __('Sale') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Refund') }}</th>
                <th scope="col">{{ __('Created by') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($returns as $return)
                @php
                    $search = strtolower($return->return_number.' '.($return->sale?->sale_number ?? '').' '.$return->return_type->value.' '.$return->status->value.' '.($return->creator?->name ?? ''));
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-medium">{{ $return->return_number }}</td>
                    <td>{{ $return->sale?->sale_number ?? '—' }}</td>
                    <td>{{ $return->return_type->label() }}</td>
                    <td><x-admin.enum-status-badge :status="$return->status->value" /></td>
                    <td class="tabular-nums">{{ number_format($return->refund_amount, 2) }}</td>
                    <td>{{ $return->creator?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap">{{ $return->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.commercial.pos.returns.show', $return)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-admin.empty-state icon="receipt-tax" :title="__('No returns found')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$returns" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
