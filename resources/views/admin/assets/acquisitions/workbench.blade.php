<x-admin-layout :title="__('Capitalization Workbench')" :breadcrumbs="[['label' => __('Acquisitions'), 'url' => route('admin.assets.acquisitions.dashboard', ['tab' => 'queue'])], ['label' => $candidate->candidate_number]]">
    <x-admin.page-header :title="__('Capitalization Workbench')" :description="__('Review and create fixed assets from procurement receipt.')" />

    @if ($requiresApproval && ! $candidate->approved_at)
        <x-admin.alert variant="warning" class="mb-4">
            {{ __('This capitalization requires authorized approval before assets can be created.') }}
            @can('approve', $candidate)
                <form method="POST" action="{{ route('admin.assets.acquisitions.approve', $candidate) }}" class="mt-3 inline">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Approve Capitalization') }}</button>
                </form>
            @endcan
        </x-admin.alert>
    @elseif ($candidate->approved_at)
        <x-admin.alert variant="success" class="mb-4">
            {{ __('Approved by :user on :date.', ['user' => $candidate->approver?->name ?? __('Authorized user'), 'date' => $candidate->approved_at->format('M j, Y')]) }}
        </x-admin.alert>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-1">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Receipt Details') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('GRN') }}</dt><dd>{{ $candidate->goodsReceipt?->receipt_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('PO') }}</dt><dd>{{ $candidate->purchaseOrder?->po_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd>{{ $candidate->vendor?->vendor_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Quantity') }}</dt><dd>{{ number_format($candidate->remainingQuantity(), 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Unit Cost') }}</dt><dd>{{ number_format($candidate->unit_cost, 2) }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.assets.acquisitions.capitalize', $candidate) }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="erp-label">{{ __('Quantity to Capitalize') }}</label>
                        <input type="number" name="quantity" min="1" max="{{ (int) $candidate->remainingQuantity() }}" value="{{ (int) $candidate->remainingQuantity() }}" class="erp-input w-full" required />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Asset Category') }}</label>
                        <select name="asset_category_id" class="erp-select w-full" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($candidate->asset_category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Branch') }}</label>
                        <select name="branch_id" class="erp-select w-full">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($candidate->branch_id == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Custodian') }}</label>
                        <select name="assigned_custodian_user_id" class="erp-select w-full">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label">{{ __('Asset Name') }}</label>
                        <input name="asset_name" value="{{ $candidate->goodsReceiptItem?->purchaseOrderItem?->description }}" class="erp-input w-full" required />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Manufacturer') }}</label>
                        <input name="manufacturer" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Model') }}</label>
                        <input name="model" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Useful Life (Years)') }}</label>
                        <input type="number" name="useful_life_years" min="1" value="{{ $candidate->category?->useful_life_years }}" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Residual Value') }}</label>
                        <input type="number" step="0.01" name="residual_value" value="0" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Warranty End') }}</label>
                        <input type="date" name="warranty_end" class="erp-input w-full" />
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Warranty Reference') }}</label>
                        <input name="warranty_reference" class="erp-input w-full" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="erp-btn-primary">{{ __('Capitalize Assets') }}</button>
                    <a href="{{ route('admin.assets.acquisitions.dashboard', ['tab' => 'queue']) }}" class="erp-btn-secondary">{{ __('Back') }}</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-admin-layout>
