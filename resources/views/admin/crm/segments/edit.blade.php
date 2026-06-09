<x-admin-layout :title="__('Edit segment')" :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Edit')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.crm.segments.update', $segment) }}">
            @csrf
            @method('PUT')
            @include('admin.crm.segments.partials.form', ['segment' => $segment])
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
