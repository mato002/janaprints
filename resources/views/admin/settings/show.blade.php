@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
@endphp

<x-admin-layout
    :title="$section === 'hub' ? __('Settings') : $sectionMeta['label']"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Settings'), 'url' => $hubBackUrl],
        ...($section !== 'hub' ? [['label' => $sectionMeta['label']]] : []),
    ]"
>
    @if ($section === 'hub')
        @include('admin.settings.partials.hub-control-center', [
            'controlCenter' => $controlCenter,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])
    @else
        @include('admin.settings.partials.hub-toolbar', [
            'title' => $sectionMeta['label'],
            'description' => $sectionMeta['description'] ?? __('Configure platform behaviour for your organization.'),
            'backUrl' => $hubBackUrl,
        ])

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.show', $section),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
            'branchLabel' => __('Branch context'),
            'branchEmptyLabel' => __('Company default only'),
        ])

        <x-admin.card>
            @if ($canManage)
                <form method="POST" action="{{ route('admin.settings.update', $section) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="company_id" value="{{ $companyId }}">
                    @if ($branchId)
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif

                    @include('admin.settings.partials.settings-table', ['editable' => true])

                    <div class="border-t border-erp-border pt-6">
                        <x-primary-button>{{ __('Save settings') }}</x-primary-button>
                    </div>
                </form>
            @else
                @include('admin.settings.partials.settings-table', ['editable' => false])
            @endif
        </x-admin.card>
    @endif
</x-admin-layout>
