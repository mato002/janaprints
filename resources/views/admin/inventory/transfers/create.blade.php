@php($fields = $formFields ?? [])
<x-admin-layout :title="__('Create transfer')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Store Transfers'), 'url' => route('admin.inventory.transfers.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-5xl">
        <form method="POST" action="{{ route('admin.inventory.transfers.store') }}">
            @csrf
            <div class="erp-form-grid">
                @if (($fields['warehouse_id']['visible'] ?? true))
                    <div>
                        <x-input-label for="warehouse_id" :value="$fields['warehouse_id']['label'] ?? __('From store')" />
                        <select id="warehouse_id" name="warehouse_id" class="erp-select mt-1" @required($fields['warehouse_id']['required'] ?? true)>
                            <option value="">{{ __('Select store') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if (($fields['to_warehouse_id']['visible'] ?? true))
                    <div>
                        <x-input-label for="to_warehouse_id" :value="$fields['to_warehouse_id']['label'] ?? __('To store')" />
                        <select id="to_warehouse_id" name="to_warehouse_id" class="erp-select mt-1" @required($fields['to_warehouse_id']['required'] ?? true)>
                            <option value="">{{ __('Select store') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('to_warehouse_id') == $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if (($fields['issue_date']['visible'] ?? true))
                    <div>
                        <x-input-label for="issue_date" :value="$fields['issue_date']['label'] ?? __('Transfer date')" />
                        <x-text-input id="issue_date" name="issue_date" type="date" class="block mt-1 w-full" :value="old('issue_date', now()->toDateString())" @required($fields['issue_date']['required'] ?? true) />
                    </div>
                @endif
                @if (($fields['notes']['visible'] ?? true))
                    <div class="md:col-span-2">
                        <x-input-label for="notes" :value="$fields['notes']['label'] ?? __('Notes')" />
                        <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="2" @required($fields['notes']['required'] ?? false)>{{ old('notes') }}</textarea>
                    </div>
                @endif
            </div>

            @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])

            <div class="mt-6"><x-primary-button>{{ __('Create transfer') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
