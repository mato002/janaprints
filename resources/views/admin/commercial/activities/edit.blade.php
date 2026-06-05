<x-admin-layout :title="__('Edit activity')" :breadcrumbs="[['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')], ['label' => __('Edit')]]">
    <x-admin.card class="max-w-4xl">
        <form method="POST" action="{{ route('admin.commercial.activities.update', $activity) }}" data-turbo-frame="_top">
            @csrf
            @method('PUT')
            @include('admin.commercial.activities.partials.form', ['activity' => $activity])
            <div class="mt-6 flex gap-2">
                <x-primary-button>{{ __('Update') }}</x-primary-button>
                <a href="{{ route('admin.commercial.activities.show', $activity) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
