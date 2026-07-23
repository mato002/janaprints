<x-admin-layout :title="$shell['title']" :compact-workspace="true">
    @if (! empty($showWebsiteCmsSupport))
        @include('admin.website.partials.cms-support-panel')
    @endif

    <x-admin.workspace-shell
        :title="$shell['title']"
        :description="$shell['description']"
        :primary-workspaces="$shell['primary_workspaces']"
        :active-primary="$shell['active_primary']"
        :secondary-workspaces="$shell['secondary_workspaces']"
        :active-secondary="$shell['active_secondary']"
        :secondary-toolbar-actions="$shell['secondary_toolbar_actions'] ?? []"
        :context-workspaces="$shell['context_workspaces'] ?? []"
        :active-context="$shell['active_context'] ?? null"
        :content-url="$shell['content_url']"
    >
        <x-slot:search>
            <x-admin.workspace-search-bar
                :module-title="$shell['title']"
                :module-key="$moduleKey"
            />
        </x-slot:search>
        @if (empty($shell['content_url']) && empty($shell['secondary_workspaces']))
            <x-admin.empty-state
                icon="{{ $shell['icon'] ?? 'inbox' }}"
                :title="__('Select a workspace')"
                :description="__('Choose a workspace tab above to open operational content.')"
            />
        @elseif (empty($shell['content_url']))
            <x-admin.empty-state
                icon="{{ $shell['active_secondary']['icon'] ?? 'inbox' }}"
                :title="$shell['active_secondary']['label'] ?? __('Coming soon')"
                :description="$shell['active_secondary']['description'] ?? __('This workspace is not available yet.')"
            />
        @endif
    </x-admin.workspace-shell>
</x-admin-layout>
