@if ($training['expiring_certificates']->isNotEmpty())
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        {{ __(':count certification(s) expiring within 60 days.', ['count' => $training['expiring_certificates']->count()]) }}
    </div>
@endif

<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Courses') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Program') }}</th><th>{{ __('Status') }}</th><th>{{ __('Due') }}</th><th>{{ __('Cert Expiry') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($training['assignments'] as $assignment)
                <tr>
                    <td><a href="{{ route('admin.hr.training.assignments.show', $assignment) }}" class="text-erp-primary hover:underline">{{ $assignment->program?->name }}</a></td>
                    <td>{{ $assignment->status?->label() ?? $assignment->status }}</td>
                    <td>{{ $assignment->due_date?->format('M j, Y') ?? '—' }}</td>
                    <td>{{ $assignment->certificate_expires_at?->format('M j, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No training assignments')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$training['assignments']" /></x-slot>
    </x-admin.data-table>
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Skills') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Skill') }}</th><th>{{ __('Proficiency') }}</th><th>{{ __('Acquired') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($training['skills'] as $skill)
                <tr>
                    <td>{{ $skill->skill_name }}</td>
                    <td>{{ $skill->proficiency?->label() ?? $skill->proficiency }}</td>
                    <td>{{ $skill->acquired_at?->format('M j, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state :title="__('No skills recorded')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>
