<x-admin-layout :title="__('Documents')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Documents')]]">
    <x-admin.page-header :title="__('Document Center')" :description="__('Employee contracts, IDs, statutory records, and HR files.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeDocument::class)
                <a href="{{ route('admin.hr.documents.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Upload document') }}</a>
            @endcan
            <a href="{{ route('admin.hr.documents.index') }}" class="erp-btn-secondary">{{ __('All documents') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Total Documents'), 'value' => $stats['total_documents'], 'icon' => 'document-text'],
            ['label' => __('Employees Covered'), 'value' => $stats['employees_with_documents'], 'icon' => 'identification'],
            ['label' => __('Expiring Soon'), 'value' => $stats['expiring_soon'], 'icon' => 'clock'],
            ['label' => __('Expired'), 'value' => $stats['expired'], 'icon' => 'exclamation'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6" :title="__('Renewal Alerts')">
        @if ($alerts->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No documents require renewal attention.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3">{{ __('Employee') }}</th>
                            <th class="py-2 pr-3">{{ __('Document') }}</th>
                            <th class="py-2 pr-3">{{ __('Category') }}</th>
                            <th class="py-2 pr-3">{{ __('Expires') }}</th>
                            <th class="py-2">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($alerts as $document)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">{{ $document->employee->full_name }}</td>
                                <td class="py-2 pr-3">
                                    <a href="{{ route('admin.hr.documents.show', $document) }}" class="text-indigo-600 hover:underline">{{ $document->title }}</a>
                                </td>
                                <td class="py-2 pr-3">{{ $document->category->label() }}</td>
                                <td class="py-2 pr-3">{{ $document->expires_at?->format('Y-m-d') }}</td>
                                <td class="py-2">
                                    @if ($document->isExpired())
                                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">{{ __('Expired') }}</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">{{ __('Renewal due') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
