<x-admin-layout :title="__('Returns')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Returns')]]">
    <x-admin.page-header :title="__('Asset Returns')" :description="__('Return workflow with condition capture.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetReturn::class)
                <x-admin.form-modal-link :href="route('admin.assets.custody.returns.create')">
                    {{ __('Record Return') }}
                </x-admin.form-modal-link>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('Return Date') }}</th>
                        <th>{{ __('Condition') }}</th>
                        <th>{{ __('Received By') }}</th>
                        <th>{{ __('Review') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td>{{ $return->asset?->asset_number }} — {{ $return->asset?->asset_name }}</td>
                            <td>{{ $return->return_date?->format('Y-m-d') }}</td>
                            <td>{{ $return->condition->label() }}</td>
                            <td>{{ $return->receiver?->name ?? '—' }}</td>
                            <td>{{ $return->requires_review ? __('Yes') : __('No') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No returns yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($returns->hasPages())<div class="mt-4">{{ $returns->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
