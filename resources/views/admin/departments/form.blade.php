<x-admin-layout :title="$department ? __('Edit department') : __('Create department')" :breadcrumbs="[['label' => __('Departments'), 'url' => route('admin.departments.index')], ['label' => $department ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ $action }}">@csrf @if($method !== 'POST') @method($method) @endif
            @if (auth()->user()->hasRole('Super Admin'))
                <div class="mb-4"><x-input-label for="company_id" :value="__('Company')" />
                    <select name="company_id" class="block mt-1 w-full rounded-md border-gray-300" required>
                        @foreach ($companies as $c)<option value="{{ $c->id }}" @selected(old('company_id', $department?->company_id) == $c->id)>{{ $c->name }}</option>@endforeach
                    </select></div>
            @else<input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">@endif
            <div class="grid gap-4">
                <div><x-input-label for="name" :value="__('Name')" /><x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $department?->name)" required /></div>
                <div><x-input-label for="code" :value="__('Code')" /><x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $department?->code)" required /></div>
                <div><x-input-label for="description" :value="__('Description')" /><textarea name="description" class="block mt-1 w-full rounded-md border-gray-300">{{ old('description', $department?->description) }}</textarea></div>
                <label class="flex gap-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $department?->is_active ?? true))> {{ __('Active') }}</label>
            </div>
            <div class="mt-6"><x-primary-button>{{ __('Save') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
