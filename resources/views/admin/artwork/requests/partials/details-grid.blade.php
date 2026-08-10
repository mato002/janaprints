<div class="artwork-detail-card">
    <h2 class="artwork-detail-card__title">{{ __('Details') }}</h2>
    <dl class="artwork-detail-meta-grid">
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label">{{ __('Priority') }}</dt>
            <dd class="artwork-detail-meta-grid__value">
                <x-admin.artwork-priority-badge :priority="$request->priority" />
            </dd>
        </div>
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label">{{ __('Due') }}</dt>
            <dd class="artwork-detail-meta-grid__value">{{ $request->due_date?->format('d M Y') ?? '—' }}</dd>
        </div>
        <div class="artwork-detail-meta-grid__item">
            <dt class="artwork-detail-meta-grid__label">{{ __('Designer') }}</dt>
            <dd class="artwork-detail-meta-grid__value">{{ $request->assignedDesigner?->name ?? '—' }}</dd>
        </div>
    </dl>
    @if ($request->description)
        <div class="mt-4 border-t border-slate-100 pt-4">
            <p class="text-xs font-medium text-slate-500">{{ __('Description') }}</p>
            <p class="mt-1 text-sm text-slate-700">{{ $request->description }}</p>
        </div>
    @endif
</div>
