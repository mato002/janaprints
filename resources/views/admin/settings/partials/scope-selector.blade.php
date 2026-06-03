@props([
    'action',
    'companyId',
    'branchId' => null,
    'companies',
    'branches',
    'branchLabel' => __('Branch scope'),
    'branchEmptyLabel' => __('Company default'),
])

@if ($companies->count() > 1 || $branches->isNotEmpty())
    <x-admin.card class="mb-4">
        <form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-4">
            @if ($companies->count() > 1)
                <div class="min-w-[12rem] flex-1">
                    <x-input-label for="company_id" :value="__('Company')" />
                    <select id="company_id" name="company_id" class="erp-select mt-1 w-full" onchange="this.form.submit()">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="company_id" value="{{ $companyId }}">
            @endif

            @if ($branches->isNotEmpty())
                <div class="min-w-[12rem] flex-1">
                    <x-input-label for="branch_id" :value="$branchLabel" />
                    <select id="branch_id" name="branch_id" class="erp-select mt-1 w-full" onchange="this.form.submit()">
                        <option value="">{{ $branchEmptyLabel }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>
    </x-admin.card>
@endif
