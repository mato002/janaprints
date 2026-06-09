@props(['rankings'])

<x-admin.card class="mb-6">
    <h3 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Rankings') }}</h3>
    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($rankings as $block)
            @include('admin.reports.production.partials.simple-table', $block)
        @endforeach
    </div>
</x-admin.card>
