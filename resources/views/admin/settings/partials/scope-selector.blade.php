@props([
    'action',
    'companyId',
    'branchId' => null,
    'companies',
    'branches',
    'branchLabel' => __('Branch scope'),
    'branchEmptyLabel' => __('Company default'),
    'compact' => false,
])

@if ($companies->count() > 1 || $branches->isNotEmpty())
    @if ($compact)
        <form method="GET" action="{{ $action }}" class="mb-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
    @else
        <x-admin.card class="mb-4">
            <form method="GET" action="{{ $action }}" class="flex flex-wrap items-end gap-4">
    @endif
            @if ($companies->count() > 1)
                <div @class(['flex items-center gap-2' => $compact, 'min-w-[12rem]' => ! $compact, 'flex-1' => ! $compact])>
                    <x-input-label for="company_id" :value="__('Company')" @class(['shrink-0 text-xs font-medium text-slate-500' => $compact]) />
                    <select id="company_id" name="company_id" @class(['erp-select', 'w-full min-w-[10rem] py-1.5 text-sm' => $compact, 'mt-1 w-full' => ! $compact]) onchange="this.form.submit()">
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($companyId === $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="company_id" value="{{ $companyId }}">
            @endif

            @if ($branches->isNotEmpty())
                <div @class(['flex items-center gap-2' => $compact, 'min-w-[12rem]' => ! $compact, 'flex-1' => ! $compact])>
                    @if ($compact)
                        <label for="branch_id" class="shrink-0 text-xs font-medium text-slate-500">{{ $branchLabel }}</label>
                    @else
                        <x-input-label for="branch_id" :value="$branchLabel" />
                    @endif
                    <select id="branch_id" name="branch_id" @class(['erp-select', 'w-full min-w-[10rem] py-1.5 text-sm' => $compact, 'mt-1 w-full' => ! $compact]) onchange="this.form.submit()">
                        <option value="">{{ $branchEmptyLabel }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </form>
    @unless ($compact)
        </x-admin.card>
    @endunless
@endif
