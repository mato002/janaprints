<x-admin-layout :title="$template->name" :breadcrumbs="[['label' => __('Print Templates'), 'url' => route('admin.production.print-templates.index')], ['label' => $template->name]]">
    <x-admin.page-header :title="$template->name" :description="$template->code">
        <x-slot name="actions">
            @can('update', $template)
                <a href="{{ route('admin.production.print-templates.edit', $template) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 flex flex-wrap gap-2">
        <x-admin.status-badge :tone="$template->is_active ? 'green' : 'slate'">{{ $template->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge>
        <span class="text-sm text-slate-500">{{ $preview['category_label'] ?? '' }}</span>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($preview['sections'] as $sectionKey => $fields)
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ str_replace('_', ' ', ucfirst($sectionKey)) }}</h3>
                <dl class="space-y-2 text-sm">
                    @foreach ($fields as $field)
                        <div class="flex justify-between gap-3"><dt class="text-slate-500">{{ $field['label'] }}</dt><dd class="text-right font-medium">{{ $field['value'] }}</dd></div>
                    @endforeach
                </dl>
            </x-admin.card>
        @endforeach
    </div>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 font-medium">{{ __('Specification defaults preview') }}</h3>
        <p class="mb-3 text-sm text-slate-600">{{ __('These values pre-fill production specifications. All fields remain editable after application.') }}</p>
        <pre class="overflow-x-auto rounded-md bg-slate-50 p-3 text-xs">{{ json_encode($preview['specification_defaults'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </x-admin.card>
</x-admin-layout>
