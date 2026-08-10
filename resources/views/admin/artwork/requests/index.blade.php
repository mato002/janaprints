@php
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Sales\SalesDeskViews;

    $designerOperator = auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $embeddedInWorkspace = WorkspaceEmbed::inWorkspaceContext();
@endphp

<x-admin-layout
    :title="__('Artwork requests')"
    :breadcrumbs="$designerOperator || $embeddedInWorkspace
        ? [['label' => __('Designer Desk')], ['label' => __('All Requests')]]
        : [['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => __('Requests')]]"
    :compact-workspace="$designerOperator || $embeddedInWorkspace"
>
    @unless ($embeddedInWorkspace)
        @if ($designerOperator)
            @include('admin.artwork.desk.partials.desk-mode-nav')
        @else
            @include('admin.sales.desk.partials.desk-mode-nav', ['activeSalesView' => SalesDeskViews::ARTWORK])
        @endif
    @endunless

    <x-admin.page-header :title="__('Artwork requests')" :description="__('All design requests for your branch.')">
        @can('create', App\Models\Artwork\ArtworkRequest::class)
            <x-admin.form-modal-link :href="route('admin.artwork.create')">{{ __('New request') }}</x-admin.form-modal-link>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search artwork…')"
        export-filename="artwork-requests"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'draft', 'label' => __('Draft')],
            ['id' => 'in_design', 'label' => __('In Design')],
            ['id' => 'pending_approval', 'label' => __('Pending')],
            ['id' => 'approved', 'label' => __('Approved')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Request') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Due') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($requests as $item)
                @php
                    $search = strtolower($item->request_number.' '.$item->title.' '.($item->customer?->company_name ?? '').' '.$item->status->value);
                    $chip = strtolower($item->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search), @js($chip))">
                    <td>
                        <div class="font-medium">{{ $item->title }}</div>
                        <div class="text-[11px] text-slate-500">{{ $item->request_number }}</div>
                    </td>
                    <td>{{ $item->customer?->company_name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$item->status->value" /></td>
                    <td class="hidden md:table-cell">{{ $item->due_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.artwork.show', $item)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $item)
                                <x-admin.table-row-action :href="route('admin.artwork.edit', $item)" data-erp-modal-open>{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="color-swatch" :title="__('No artwork requests yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$requests" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
