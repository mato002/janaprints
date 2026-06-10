<x-admin-layout :title="$company ? __('Edit company') : __('Create company')" :breadcrumbs="[['label' => __('Companies'), 'url' => route('admin.companies.index')], ['label' => $company ? __('Edit') : __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">@csrf @if($method !== 'POST') @method($method) @endif
            @include('admin.companies.partials.form-fields', [
                'company' => $company ?? null,
                'logoUrl' => $logoUrl ?? null,
                'faviconUrl' => $faviconUrl ?? null,
            ])
            <div class="mt-6 flex gap-3"><x-primary-button>{{ __('Save') }}</x-primary-button><a href="{{ route('admin.companies.index') }}">{{ __('Cancel') }}</a></div>
        </form>
        @if ($company)
            @can('delete', $company)
                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" class="mt-4" onsubmit="return confirm('{{ __('Delete?') }}')">@csrf @method('DELETE')<button class="text-red-600 text-sm">{{ __('Delete') }}</button></form>
            @endcan
        @endif
    </div>
</x-admin-layout>
