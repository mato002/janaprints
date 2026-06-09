<x-admin-layout :title="__('Certificate Tracking')">
    <x-admin.page-header :title="__('Certificate Tracking')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <select name="status" class="erp-input">
                <option value="">{{ __('All certificates') }}</option>
                <option value="valid" @selected(($filters['status'] ?? '') === 'valid')>{{ __('Valid') }}</option>
                <option value="expiring" @selected(($filters['status'] ?? '') === 'expiring')>{{ __('Expiring Soon') }}</option>
                <option value="expired" @selected(($filters['status'] ?? '') === 'expired')>{{ __('Expired') }}</option>
            </select>
            <button type="submit" class="erp-btn-primary">{{ __('Filter') }}</button>
        </form>
    </x-admin.card>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Program') }}</th>
                <th>{{ __('Certificate') }}</th>
                <th>{{ __('Expires') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($certificates as $assignment)
                <tr>
                    <td>{{ $assignment->employee?->full_name }}</td>
                    <td>
                        <a href="{{ route('admin.hr.training.programs.show', $assignment->program) }}" class="text-erp-primary hover:underline">
                            {{ $assignment->program?->title }}
                        </a>
                    </td>
                    <td>{{ $assignment->certificate_reference }}</td>
                    <td>{{ $assignment->certificate_expires_at?->format('Y-m-d') ?? '—' }}</td>
                    <td>
                        @if ($assignment->certificate_expires_at?->isPast())
                            <span class="text-red-600">{{ __('Expired') }}</span>
                        @elseif ($assignment->certificate_expires_at?->lte(now()->addDays(30)))
                            <span class="text-amber-600">{{ __('Expiring Soon') }}</span>
                        @else
                            <span class="text-emerald-600">{{ __('Valid') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No certificates found.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $certificates->links() }}</div>
</x-admin-layout>
