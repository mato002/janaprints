<x-admin-layout :title="__('Accounting Periods')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Accounting Periods')]]">
    <x-admin.page-header :title="__('Accounting Periods')" :description="__('Company fiscal years and monthly periods · fiscal year starts in :month', ['month' => \Carbon\Carbon::create(null, $startMonth, 1)->format('F')])">
        <x-slot name="actions">
            @can('create', App\Models\Accounting\FiscalYear::class)
                <a href="{{ route('admin.accounting.periods.create') }}" class="erp-btn-primary">{{ __('Generate fiscal year') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if ($currentPeriod)
        <x-admin.card class="mb-4 border-erp-accent/30 bg-erp-accent/5">
            <p class="text-sm">
                <span class="font-semibold text-erp-primary">{{ __('Current period') }}:</span>
                {{ $currentPeriod->name }} ({{ $currentPeriod->code }})
                · {{ $currentPeriod->fiscalYear->name }}
            </p>
        </x-admin.card>
    @endif

    <x-admin.data-table :search-placeholder="__('Search fiscal years…')" export-filename="fiscal-years">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Fiscal year') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Date range') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="hidden sm:table-cell">{{ __('Periods') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($fiscalYears as $fy)
                <tr x-show="rowVisible(@js(strtolower($fy->name.' '.$fy->code.' '.$fy->status->value)))">
                    <td>
                        <a href="{{ route('admin.accounting.periods.fiscal-years.show', $fy) }}" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $fy->name }}</a>
                        <div class="font-mono text-[11px] text-slate-500">{{ $fy->code }} @if($fy->is_current)<span class="text-erp-accent">· {{ __('Current FY') }}</span>@endif</div>
                    </td>
                    <td class="hidden md:table-cell text-sm text-slate-600">
                        {{ $fy->start_date->format('Y-m-d') }} → {{ $fy->end_date->format('Y-m-d') }}
                    </td>
                    <td>
                        <x-admin.status-badge :variant="match($fy->status) {
                            App\Enums\FiscalYearStatus::Open => 'success',
                            App\Enums\FiscalYearStatus::YearEndPreparation => 'warning',
                            App\Enums\FiscalYearStatus::Closed => 'neutral',
                            App\Enums\FiscalYearStatus::Locked => 'danger',
                        }">{{ $fy->status->label() }}</x-admin.status-badge>
                    </td>
                    <td class="hidden sm:table-cell text-sm">{{ $fy->periods->count() }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.accounting.periods.fiscal-years.show', $fy)">{{ __('View periods') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="calendar" :title="__('No fiscal years yet')" :description="__('Generate a fiscal year to create 12 monthly accounting periods.')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
