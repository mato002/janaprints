<x-admin.modal-form
    :title="__('Create role')"
    :breadcrumbs="[
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => __('Create')],
    ]"
    maxWidth="lg"
>
    <x-admin.form-shell :action="route('admin.roles.store')">
        <div class="erp-form-grid">
            <x-admin.input
                name="name"
                :label="__('Role name')"
                :value="old('name')"
                :required="true"
                :colSpan="2"
                placeholder="{{ __('e.g. Production Supervisor') }}"
            />

            <x-admin.form-field name="clone_from" :label="__('Clone from existing role')" :colSpan="2" :help="__('Copies permissions only. Users are never cloned.')">
                <select id="clone_from" name="clone_from" class="erp-select w-full">
                    <option value="">{{ __('Start without permissions') }}</option>
                    @foreach ($cloneOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('clone_from') == $option->id)>
                            {{ $option->name }}
                            @if ($option->permissions_count > 0)
                                ({{ $option->permissions_count }} {{ __('permissions') }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </x-admin.form-field>
        </div>

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create role') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
