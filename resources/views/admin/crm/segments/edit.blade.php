<x-admin-layout :title="__('Edit segment')" :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Edit')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.crm.segments.update', $segment) }}">@csrf @method('PUT')
            <x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full mb-3" :value="old('name', $segment->name)" required />
            <x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="block mt-1 w-full mb-3" :value="old('code', $segment->code)" required />
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
