<x-admin-layout
    :title="$title"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register'), 'url' => route('admin.assets.index')],
        ['label' => $title],
    ]"
>
    <x-admin.page-header :title="$title" />
    <x-admin.card>
        <form method="POST" action="{{ $action }}" class="max-w-2xl space-y-4">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            @include('admin.assets.partials.form-fields', [
                'asset' => $asset ?? null,
                'categories' => $categories,
                'branches' => $branches,
                'users' => $users,
                'statuses' => $statuses,
            ])

            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ $asset ? __('Save Changes') : __('Register Asset') }}</button>
                <a href="{{ $asset ? route('admin.assets.show', $asset) : route('admin.assets.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
