@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<x-admin.data-table>
    <x-slot name="head">
        <tr>
            <th>{{ __('Reference') }}</th>
            <th>{{ __('Candidate') }}</th>
            <th>{{ __('Vacancy') }}</th>
            <th>{{ __('Stage') }}</th>
            <th>{{ __('Applied') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($applications as $application)
            <tr>
                <td class="font-mono text-xs">{{ $application->reference }}</td>
                <td>
                    <a href="{{ WorkspaceEmbed::url(route('admin.hr.recruitment.applications.show', $application)) }}" class="font-medium text-erp-primary hover:underline">
                        {{ $application->candidate->full_name }}
                    </a>
                </td>
                <td>{{ $application->vacancy->title }}</td>
                <td><span class="erp-badge bg-slate-100 text-slate-700">{{ $application->stage->label() }}</span></td>
                <td>{{ $application->applied_at->format('Y-m-d') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No applications found.') }}</td></tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
<div class="mt-4">{{ $applications->links() }}</div>
