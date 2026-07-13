<x-admin-layout :title="__('Job Titles')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Job Titles')]]">
    <x-admin.workspace-content-header :title="__('Job Titles')" :description="__('Standardized position titles and reporting structure for the organization.')">
        <x-slot:actions>
            <a href="{{ route('admin.job-titles.hierarchy') }}" class="erp-btn-secondary erp-btn--sm">{{ __('Organization chart') }}</a>
            @can('create', App\Models\JobTitle::class)
                <a href="{{ route('admin.job-titles.create') }}" class="erp-btn-primary erp-btn--sm">{{ __('Create job title') }}</a>
            @endcan
        </x-slot:actions>
    </x-admin.workspace-content-header>

<x-admin.data-table
        :search-placeholder="__('Search job titles…')"
        export-route="admin.job-titles.export"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="job-titles"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Title') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Department') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Level') }}</th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Reports To') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Employees') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($titles as $jobTitle)
                <tr x-show="rowVisible(@js(strtolower($jobTitle->code.' '.$jobTitle->title.' '.($jobTitle->department?->name ?? ''))))">
                    <td class="font-mono text-[11px] text-slate-500">{{ $jobTitle->code }}</td>
                    <td>
                        <div class="font-medium text-erp-primary">{{ $jobTitle->title }}</div>
                        @if ($jobTitle->approval_authority)
                            <div class="text-[11px] text-slate-500">{{ __('Approval') }}: {{ $jobTitle->approval_authority }}</div>
                        @endif
                    </td>
                    <td class="hidden lg:table-cell">{{ $jobTitle->department?->name ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $jobTitle->level->label() }}</td>
                    <td class="hidden xl:table-cell">{{ $jobTitle->reportsTo?->title ?? '—' }}</td>
                    <td class="hidden md:table-cell tabular-nums">{{ $jobTitle->employees_count }}</td>
                    <td>
                        <span class="erp-badge erp-badge--{{ $jobTitle->is_active ? 'success' : 'neutral' }}">
                            {{ $jobTitle->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $jobTitle)
                                <x-admin.table-row-action :href="route('admin.job-titles.edit', $jobTitle)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('deactivate', $jobTitle)
                                @if ($jobTitle->is_active)
                                    <form method="POST" action="{{ route('admin.job-titles.deactivate', $jobTitle) }}" class="contents" onsubmit="return confirm(@js(__('Deactivate this job title?')))">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="erp-table-row-action w-full text-left text-rose-700">{{ __('Deactivate') }}</button>
                                    </form>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state icon="badge-check" :title="__('No job titles yet')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
