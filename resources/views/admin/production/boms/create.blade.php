<x-admin.modal-form
    :title="__('New bill of materials')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Bills of Materials'), 'url' => route('admin.production.boms.index')],
        ['label' => __('New BOM')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.production.boms.store')" class="space-y-4">
        @if ($returnJobCard ?? null)
            <input type="hidden" name="job_card_id" value="{{ $returnJobCard->getRouteKey() }}">
            <div class="rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-950">
                {{ __('Creating BOM for job :job. After saving, return to Materials and generate requirements.', ['job' => $returnJobCard->job_card_number]) }}
            </div>
        @endif

        @include('admin.production.boms._form', [
            'bom' => null,
            'preselectedFinishedItemId' => $preselectedFinishedItemId ?? null,
            'prefilledName' => $prefilledName ?? null,
        ])

        <x-admin.form-actions :cancel-url="route('admin.production.boms.index')">
            <x-primary-button>{{ __('Create BOM') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
