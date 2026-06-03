<x-admin-layout :title="__('Create segment')" :breadcrumbs="[['label' => __('Segments'), 'url' => route('admin.crm.segments.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="{{ route('admin.crm.segments.store') }}">@csrf
            @if (auth()->user()->hasRole('Super Admin'))
                <div class="mb-4"><x-input-label for="company_id" :value="__('Company')" />
                    <select name="company_id" class="block mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select></div>
            @else<input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">@endif
            <x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full mb-3" required />
            <x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="block mt-1 w-full mb-3" required />
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </form>
    </div>
</x-admin-layout>
