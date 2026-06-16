<x-admin.modal-form
    :title="$company ? __('Edit company') : __('Create company')"
    :breadcrumbs="[['label' => __('Companies'), 'url' => route('admin.companies.index')], ['label' => $company ? __('Edit') : __('Create')]]"
>
    <x-admin.form-shell :action="$action" :method="$method" enctype="multipart/form-data">
        @include('admin.companies.partials.form-fields', [
            'company' => $company ?? null,
            'logoUrl' => $logoUrl ?? null,
            'faviconUrl' => $faviconUrl ?? null,
        ])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (! request()->header('Turbo-Frame'))
                <a href="{{ route('admin.companies.index') }}">{{ __('Cancel') }}</a>
            @endif
        </x-admin.form-modal-actions>
    </x-admin.form-shell>

    @if ($company)
        @can('delete', $company)
            <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" class="mt-4" onsubmit="return confirm('{{ __('Delete?') }}')">
                @csrf
                @method('DELETE')
                <button class="text-red-600 text-sm">{{ __('Delete') }}</button>
            </form>
        @endcan
    @endif
</x-admin.modal-form>
