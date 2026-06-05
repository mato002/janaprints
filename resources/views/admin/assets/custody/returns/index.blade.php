<x-admin-layout :title="__('Returns')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Returns')]]">
    <x-admin.page-header :title="__('Asset Returns')" :description="__('Return workflow with condition capture.')" />

    @can('create', \App\Models\Assets\AssetReturn::class)
        <x-admin.card class="mb-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Record Return') }}</h3>
            <form method="POST" action="{{ route('admin.assets.custody.returns.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Asset') }}</label>
                    <select name="fixed_asset_id" class="erp-select w-full" required>
                        <option value="">{{ __('Select asset…') }}</option>
                        @foreach (\App\Models\Assets\FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number']) as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Return Date') }}</label>
                    <input type="date" name="return_date" value="{{ now()->toDateString() }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Condition') }}</label>
                    <select name="condition" class="erp-select w-full" required>
                        @foreach ($conditions as $condition)
                            <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="erp-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="erp-input w-full" rows="2"></textarea>
                </div>
                <div>
                    <button type="submit" class="erp-btn-primary">{{ __('Record Return') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endcan

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
