<x-admin-layout :title="__('Vacancies')">
    <x-admin.page-header :title="__('Vacancies')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.recruitment.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\Vacancy::class)
                <a href="{{ route('admin.hr.recruitment.vacancies.create') }}" class="erp-btn-primary">{{ __('New vacancy') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Positions') }}</th>
                <th>{{ __('Applications') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($vacancies as $vacancy)
                <tr>
                    <td class="font-mono text-xs">{{ $vacancy->reference }}</td>
                    <td><a href="{{ route('admin.hr.recruitment.vacancies.show', $vacancy) }}" class="font-medium text-erp-primary hover:underline">{{ $vacancy->title }}</a></td>
                    <td>{{ $vacancy->filled_count }}/{{ $vacancy->positions }}</td>
                    <td>{{ $vacancy->applications_count }}</td>
                    <td><span class="erp-badge bg-slate-100 text-slate-700">{{ $vacancy->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No vacancies found.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $vacancies->links() }}</div>
</x-admin-layout>
