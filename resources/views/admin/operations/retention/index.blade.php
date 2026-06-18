<x-admin-layout
    :title="__('Data Retention')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('Data Retention')],
    ]"
>
    <x-admin.page-header
        :title="__('Data Retention')"
        :description="__('Retention and archival governance — control how long ERP records remain active before archive and deletion.')"
    />

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.kpi-widget :label="__('Retention Policies')" :value="$metrics['total']" icon="archive" />
        <x-admin.kpi-widget :label="__('Legal Holds')" :value="$metrics['legal_holds']" icon="shield-check" />
        <x-admin.kpi-widget :label="__('Longest Retention')" :value="trans_choice(':count day|:count days', $metrics['longest_retention'], ['count' => $metrics['longest_retention']])" icon="clock" />
        <x-admin.kpi-widget :label="__('Shortest Retention')" :value="trans_choice(':count day|:count days', $metrics['shortest_retention'], ['count' => $metrics['shortest_retention']])" icon="clock" />
    </div>

    <div class="space-y-4">
        @foreach ($policies as $policy)
            <section>
                <x-admin.card>
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-erp-primary">{{ $policy->domain->label() }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $policy->domain->description() }}</p>
                    </div>
                    @if ($policy->legal_hold)
                        <x-admin.status-badge variant="warning">{{ __('Legal Hold') }}</x-admin.status-badge>
                    @else
                        <x-admin.status-badge variant="success">{{ __('Active Policy') }}</x-admin.status-badge>
                    @endif
                </div>

                @if ($canManage)
                    <form method="POST" action="{{ route('admin.operations.retention.update', $policy) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="erp-label">{{ __('Archive After (days)') }}</label>
                            <input
                                type="number"
                                name="archive_after_days"
                                value="{{ old('archive_after_days.'.$policy->id, $policy->archive_after_days) }}"
                                min="1"
                                class="erp-input w-full text-sm"
                                placeholder="{{ __('Optional') }}"
                            />
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Delete After (days)') }}</label>
                            <input
                                type="number"
                                name="delete_after_days"
                                value="{{ old('delete_after_days.'.$policy->id, $policy->delete_after_days) }}"
                                min="1"
                                class="erp-input w-full text-sm"
                                placeholder="{{ __('Optional') }}"
                            />
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Retention Period (days)') }}</label>
                            <input
                                type="number"
                                name="retention_period_days"
                                value="{{ old('retention_period_days.'.$policy->id, $policy->retention_period_days) }}"
                                min="1"
                                required
                                class="erp-input w-full text-sm"
                            />
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="hidden" name="legal_hold" value="0">
                                <input
                                    type="checkbox"
                                    name="legal_hold"
                                    value="1"
                                    class="rounded border-erp-border text-erp-accent"
                                    @checked(old('legal_hold.'.$policy->id, $policy->legal_hold))
                                />
                                {{ __('Legal Hold') }}
                            </label>
                        </div>
                        <div class="flex items-end">
                            @if ($policy->legal_hold)
                                <label class="mb-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <input type="checkbox" name="release_legal_hold" value="1" class="rounded border-erp-border text-erp-accent" />
                                    {{ __('Confirm legal hold release') }}
                                </label>
                            @endif
                            <button type="submit" class="erp-btn-primary text-sm">{{ __('Save Policy') }}</button>
                        </div>
                    </form>
                @else
                    <dl class="grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <dt class="text-slate-500">{{ __('Archive After') }}</dt>
                            <dd class="mt-1 font-medium text-erp-primary">{{ $policy->archiveAfterLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Delete After') }}</dt>
                            <dd class="mt-1 font-medium text-erp-primary">{{ $policy->deleteAfterLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Retention Period') }}</dt>
                            <dd class="mt-1 font-medium text-erp-primary">{{ $policy->retentionPeriodLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Legal Hold') }}</dt>
                            <dd class="mt-1 font-medium text-erp-primary">{{ $policy->legal_hold ? __('Active') : __('Inactive') }}</dd>
                        </div>
                    </dl>
                @endif

                @if ($policy->updatedBy)
                    <p class="mt-3 text-xs text-slate-400">
                        {{ __('Last updated by :user', ['user' => $policy->updatedBy->name]) }}
                        · {{ $policy->updated_at?->diffForHumans() }}
                    </p>
                @endif
                </x-admin.card>
            </section>
        @endforeach
    </div>
</x-admin-layout>
