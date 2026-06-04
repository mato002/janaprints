<x-admin.card>
    <div class="mb-3 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ $title }}</h3>
        @can($permission)
            <a href="{{ route('admin.crm.customers.show', ['customer' => $customer, 'tab' => $tab]) }}" class="text-xs text-erp-accent hover:text-erp-accent-hover">{{ __('View all') }}</a>
        @endcan
    </div>
    @can($permission)
        @forelse ($items as $item)
            @include($rowView, ['item' => $item])
        @empty
            <p class="text-sm text-slate-500">{{ $empty }}</p>
        @endforelse
    @else
        <p class="text-sm text-slate-500">{{ __('You do not have permission to view this module.') }}</p>
    @endcan
</x-admin.card>
