<x-admin-layout :title="__('Work centers')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => __('Work centers')]]">
    <x-admin.page-header :title="__('Work centers & stages')" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Work centers') }}</h3>
            <ul class="text-sm space-y-2">
                @forelse ($workCenters as $center)
                    <li>{{ $center->name }} <span class="text-slate-500">({{ $center->code }})</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No work centers. Run Production foundation seeder.') }}</li>
                @endforelse
            </ul>
            <div class="mt-4">{{ $workCenters->links() }}</div>
        </x-admin.card>
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Production stages') }}</h3>
            <ul class="text-sm space-y-2">
                @forelse ($stages as $stage)
                    <li>{{ $stage->sort_order }}. {{ $stage->name }} <span class="text-slate-500">({{ $stage->code }})</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No stages configured.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
