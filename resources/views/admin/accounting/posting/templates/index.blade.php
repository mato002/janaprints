<x-admin-layout :title="__('Posting templates')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Posting templates')]]">
    <x-admin.page-header :title="__('Posting templates')" :description="__('Reusable debit/credit line definitions for automated journals.')" />

    <x-admin.data-table
        :search-placeholder="__('Search templates…')"
        export-route="admin.accounting.exports"
        :export-route-params="['listing' => 'posting-templates']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="posting-templates"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Module') }}</th>
                <th scope="col">{{ __('Lines') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($templates as $template)
                <tr x-show="rowVisible(@js(strtolower($template->code.' '.$template->name)))">
                    <td class="font-mono text-sm">{{ $template->code }}</td>
                    <td class="text-sm font-medium">{{ $template->name }}</td>
                    <td class="text-sm">{{ $template->module->label() }}</td>
                    <td class="text-sm">{{ $template->lines_count }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.accounting.posting.templates.show', $template)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="document-duplicate" :title="__('No posting templates yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$templates" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
