@php($notes = $tabData['notes'])

<x-admin.card class="mb-4" id="add-note">
    <h3 class="mb-3 font-medium">{{ __('Add note') }}</h3>
    @can('update', $customer)
        <form method="POST" action="{{ route('admin.crm.customers.notes.store', $customer) }}" data-turbo-frame="erp-main">
            @csrf
            <textarea name="note" class="erp-input w-full" rows="3" required placeholder="{{ __('Write a note…') }}"></textarea>
            <button type="submit" class="erp-btn-primary mt-2 text-sm">{{ __('Save note') }}</button>
        </form>
    @else
        <p class="text-sm text-slate-500">{{ __('You do not have permission to add notes.') }}</p>
    @endcan
</x-admin.card>

<div class="space-y-3">
    @forelse ($notes as $note)
        <x-admin.card>
            <p class="text-sm text-erp-primary">{{ $note->note }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ $note->user?->name }} · {{ $note->created_at?->format('Y-m-d H:i') }}</p>
            @can('update', $customer)
                <form method="POST" action="{{ route('admin.crm.customers.notes.destroy', [$customer, $note]) }}" class="mt-2" data-turbo-confirm="{{ __('Delete this note?') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-600">{{ __('Delete') }}</button>
                </form>
            @endcan
        </x-admin.card>
    @empty
        <x-admin.empty-state :title="__('No notes')" :description="__('Notes you add will appear here.')" />
    @endforelse
</div>

@if ($notes->hasPages())
    <div class="mt-4">
        <x-admin.table-pagination :paginator="$notes" />
    </div>
@endif
