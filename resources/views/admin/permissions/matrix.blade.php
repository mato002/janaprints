@php
    use App\Support\Navigation\WorkspaceEmbed;

    $embedded = WorkspaceEmbed::inWorkspaceContext();
@endphp

<x-admin-layout
    :title="__('Permission Matrix')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Permission Matrix')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Permission Matrix'),
        'description' => null,
        'backUrl' => route('admin.access-control.index'),
    ])

    <x-admin.card class="mb-3 !px-4 !py-3">
        <form
            method="GET"
            action="{{ WorkspaceEmbed::url(route('admin.access-control.matrix')) }}"
            class="flex flex-wrap items-end gap-3"
            @if ($embedded) data-turbo-frame="module-workspace-content" @endif
        >
            @if ($embedded)
                <input type="hidden" name="embedded" value="1">
            @endif
            <div class="min-w-[12rem] flex-1">
                <x-input-label for="role" :value="__('Security group')" class="!text-xs" />
                <select id="role" name="role" class="erp-select mt-1 w-full !py-1.5 !text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Select a role') }}</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->name }}" @selected($selectedRole?->name === $roleOption->name)>{{ $roleOption->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($selectedRole)
                <a href="{{ WorkspaceEmbed::url(route('admin.roles.show', $selectedRole)) }}" class="erp-btn-secondary !px-3 !py-1.5 text-xs" data-turbo-action="advance" @if ($embedded) data-turbo-frame="erp-main" @endif>{{ __('Edit access rights') }}</a>
            @endif
        </form>
    </x-admin.card>

    @if ($selectedRole && $workspace)
        <div class="mb-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <h2 class="text-lg font-semibold text-erp-primary">{{ $selectedRole->name }}</h2>
            <p class="text-xs text-slate-500">
                {{ __('Modules') }}: <span class="font-medium text-slate-700">{{ number_format($roleSummary['modules_enabled']) }}</span>
                <span class="mx-1.5 text-slate-300">·</span>
                {{ __('Permissions') }}: <span class="font-medium text-slate-700">{{ number_format($roleSummary['permissions_enabled']) }}</span>
                <span class="mx-1.5 text-slate-300">·</span>
                {{ $selectedRole->updated_at?->format('M j, Y') ?? '—' }}
            </p>
        </div>

        @include('admin.access-control.partials.matrix-workspace', [
            'workspace' => $workspace,
            'editable' => false,
            'storageKey' => 'erp.permissionMatrix.role.'.$selectedRole->id,
        ])
    @else
        <x-admin.empty-state
            icon="lock-closed"
            :title="__('Select a security group')"
            :description="__('Choose a role above to view its access matrix.')"
        />
    @endif
</x-admin-layout>
