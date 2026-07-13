<x-admin-layout :title="__('Ink Profiles')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Ink Intelligence'), 'url' => route('admin.printing-intelligence.ink')],
    ['label' => __('Ink Profiles')],
]">
    <x-admin.page-header
        :title="__('Ink Profiles')"
        :description="__('Maintain ink costing profiles for PI3 ink estimation. No inventory or accounting mutations.')"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.printing-intelligence.ink') }}" class="erp-btn-secondary">{{ __('Back to Ink Intelligence') }}</a>
        </x-slot>
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

@if ($canManage)
        <x-admin.card class="mb-4" x-data="{ open: {{ $errors->any() && ! request()->query('edit') ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h3 class="font-medium">{{ __('Create Ink Profile') }}</h3>
                <button type="button" class="erp-btn-secondary text-xs" @click="open = !open" x-text="open ? '{{ __('Hide form') }}' : '{{ __('Show form') }}'"></button>
            </div>
            <form method="POST" action="{{ route('admin.printing-intelligence.ink-profiles.store') }}" x-show="open" x-cloak>
                @csrf
                @include('admin.printing-intelligence.ink-profiles.partials.form-fields', [
                    'inkTypes' => $inkTypes,
                    'inventoryItems' => $inventoryItems,
                ])
                <div class="mt-4">
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Create profile') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endif

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Ink Type') }}</th>
                        <th>{{ __('Inventory Item') }}</th>
                        <th>{{ __('Cartridge Cost') }}</th>
                        <th>{{ __('Estimated ml') }}</th>
                        <th>{{ __('Cost/ml') }}</th>
                        <th>{{ __('Yield Pages') }}</th>
                        <th>{{ __('Yield m²') }}</th>
                        <th>{{ __('Status') }}</th>
                        @if ($canManage)
                            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $profile)
                        <tr @class(['bg-slate-50' => ! $profile['active']])>
                            <td class="font-medium">{{ $profile['name'] }}</td>
                            <td>{{ $profile['ink_type'] }}</td>
                            <td>{{ $profile['inventory_item'] ?? '—' }}</td>
                            <td>{{ number_format((float) $profile['cartridge_cost'], 2) }}</td>
                            <td>{{ $profile['estimated_ml'] !== null ? number_format((float) $profile['estimated_ml'], 3) : '—' }}</td>
                            <td>{{ $profile['cost_per_ml'] !== null ? number_format((float) $profile['cost_per_ml'], 4) : '—' }}</td>
                            <td>{{ $profile['yield_per_page'] !== null ? number_format((float) $profile['yield_per_page'], 4) : '—' }}</td>
                            <td>{{ $profile['yield_per_sq_m'] !== null ? number_format((float) $profile['yield_per_sq_m'], 4) : '—' }}</td>
                            <td>
                                <x-admin.status-badge :variant="$profile['active'] ? 'success' : 'draft'">
                                    {{ $profile['active'] ? __('Active') : __('Inactive') }}
                                </x-admin.status-badge>
                            </td>
                            @if ($canManage)
                                <td class="erp-table-actions-col align-top">
                                    <details class="text-xs" @if((string) request()->query('edit') === (string) $profile['id']) open @endif>
                                        <summary class="cursor-pointer text-erp-primary font-medium">{{ __('Edit') }}</summary>
                                        <div class="mt-3 min-w-[18rem] rounded border border-slate-200 bg-white p-3 shadow-sm">
                                            <form method="POST" action="{{ route('admin.printing-intelligence.ink-profiles.update', $profile['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                @include('admin.printing-intelligence.ink-profiles.partials.form-fields', [
                                                    'profile' => $profile,
                                                    'inkTypes' => $inkTypes,
                                                    'inventoryItems' => $inventoryItems,
                                                ])
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Save') }}</button>
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('admin.printing-intelligence.ink-profiles.destroy', $profile['id']) }}" class="mt-2" onsubmit="return confirm(@js($profile['used_by_estimates'] ? __('Deactivate this profile? It is referenced by ink estimates.') : __('Remove this ink profile?')))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="erp-btn-secondary text-xs text-red-700">
                                                    {{ $profile['used_by_estimates'] ? __('Deactivate') : __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManage ? 10 : 9 }}" class="py-8 text-center text-slate-500">
                                {{ __('No ink profiles configured.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
