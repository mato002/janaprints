@php
    use App\Support\Crm\CrmDeskViews;
    use App\Models\Crm\Customer;
    use App\Models\Crm\Lead;
    use App\Models\Crm\CustomerSegment;
    use App\Models\Crm\CustomerActivity;
    use App\Support\Navigation\WorkspaceEmbed;

    // Inside the module workspace shell, modes live on the fixed context tab bar.
    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = CrmDeskViews::normalize($activeCrmView ?? request('view'));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => CrmDeskViews::CUSTOMERS,
            'label' => __('Customers'),
            'url' => CrmDeskViews::customersUrl(),
            'visible' => $user?->can('viewAny', Customer::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::LEADS,
            'label' => __('Leads'),
            'url' => route('admin.crm.leads.index'),
            'visible' => $user?->can('viewAny', Lead::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::ACTIVITIES,
            'label' => __('Activities'),
            'url' => route('admin.commercial.activities.index'),
            'visible' => $user?->can('viewAny', CustomerActivity::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::SEGMENTS,
            'label' => __('Segments'),
            'url' => route('admin.crm.segments.index'),
            'visible' => $user?->can('viewAny', CustomerSegment::class) ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="workspace-context-tabs" aria-label="{{ __('CRM desk modes') }}">
        @foreach ($modes as $mode)
            <a
                href="{{ WorkspaceEmbed::url($mode['url']) }}"
                @class([
                    'workspace-context-tab',
                    'workspace-context-tab--active' => $mode['key'] === $active,
                ])
                data-turbo-frame="{{ $frame }}"
                data-turbo-action="advance"
            >{{ $mode['label'] }}</a>
        @endforeach
    </nav>
@endif
