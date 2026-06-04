<div class="crm-360__tab-stack">
    @can('create', App\Models\Crm\CustomerActivity::class)
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title">{{ __('Log activity') }}</h2>
            <form method="POST" action="{{ route('admin.crm.customers.activities.store', $customer) }}" class="crm-360__form-grid">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Type') }}</label>
                    <select name="activity_type" class="erp-select w-full text-sm">
                        @foreach (App\Enums\ActivityType::cases() as $t)
                            <option value="{{ $t->value }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Subject') }}</label>
                    <x-text-input name="subject" class="w-full" required />
                </div>
                <div>
                    <label class="erp-label">{{ __('When') }}</label>
                    <x-text-input name="activity_at" type="datetime-local" class="w-full" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                </div>
                <div class="sm:col-span-3">
                    <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Log activity') }}</x-admin.crm-btn>
                </div>
            </form>
        </section>
    @endcan

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title">{{ __('Activity history') }}</h2>
            @can('viewAny', App\Models\Crm\CustomerActivity::class)
                <x-admin.crm-btn
                    variant="outline"
                    size="sm"
                    :href="route('admin.commercial.activities.index', ['customer_id' => $customer->id])"
                    data-turbo-frame="erp-main"
                >{{ __('All activities') }}</x-admin.crm-btn>
            @endcan
        </div>

        @php
            $activityGroups = $customer->activities->sortByDesc('activity_at')->groupBy(fn ($a) => ucfirst(str_replace('_', ' ', $a->activity_type->value)));
        @endphp

        @forelse ($activityGroups as $typeLabel => $group)
            <div class="crm-360__activity-group">
                <h3 class="crm-360__activity-type">{{ $typeLabel }}</h3>
                <ul class="crm-360__feed" role="list">
                    @foreach ($group as $activity)
                        <li class="crm-360__feed-item">
                            <div class="crm-360__feed-head">
                                @can('view', $activity)
                                    <a href="{{ route('admin.commercial.activities.show', $activity) }}" class="crm-360__feed-title" data-turbo-frame="erp-main">{{ $activity->subject }}</a>
                                @else
                                    <span class="crm-360__feed-title">{{ $activity->subject }}</span>
                                @endcan
                                <time class="crm-360__feed-time">{{ $activity->activity_at?->diffForHumans() }}</time>
                            </div>
                            <p class="crm-360__feed-meta">{{ $activity->user?->name ?? __('System') }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="crm-360__empty-inline">{{ __('No activities logged yet') }}</p>
        @endforelse
    </section>
</div>
