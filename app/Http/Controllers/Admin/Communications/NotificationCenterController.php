<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReadStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\ErpNotification;
use App\Models\User;
use App\Support\Communications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ErpNotification::class);

        $adminScope = $request->user()->can('communications.notifications.admin');
        $filters = $request->only(['status', 'priority', 'type', 'category', 'date_from', 'date_to', 'user_id', 'view']);
        $notifications = $this->notifications
            ->listForUser($request->user(), $filters, $adminScope)
            ->paginate(20)
            ->withQueryString();

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $actor = $request->user();

        $summaryQuery = fn () => $this->notifications->listForUser($actor, [], $adminScope);

        $summary = [
            'total' => (clone $summaryQuery())->count(),
            'unread' => (clone $summaryQuery())->whereHas('readState', fn ($q) => $q->where('status', NotificationReadStatus::Unread))->count(),
            'critical' => (clone $summaryQuery())->where('priority', NotificationPriority::Critical)->count(),
            'archived' => (clone $summaryQuery())->whereHas('readState', fn ($q) => $q->where('status', NotificationReadStatus::Archived))->count(),
        ];

        $prefs = $companyId
            ? $this->notifications->preferencesFor($actor, $companyId)
            : null;

        $users = $companyId
            ? User::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        $bootstrap = [
            'routes' => [
                'mark_read' => route('admin.communications.notifications.mark-read', ['notification' => '__ID__']),
                'dismiss' => route('admin.communications.notifications.dismiss', ['notification' => '__ID__']),
                'archive' => route('admin.communications.notifications.archive', ['notification' => '__ID__']),
                'bulk_read' => route('admin.communications.notifications.bulk-read'),
                'bulk_dismiss' => route('admin.communications.notifications.bulk-dismiss'),
                'preferences' => route('admin.communications.notifications.preferences.update'),
                'store' => route('admin.communications.notifications.store'),
            ],
            'can' => [
                'manage' => $request->user()->can('communications.notifications.manage'),
                'admin' => $adminScope,
                'create' => $request->user()->can('create', ErpNotification::class),
            ],
            'types' => array_map(
                fn (NotificationType $t) => ['value' => $t->value, 'label' => $t->label(), 'category' => $t->category()->value],
                NotificationType::cases(),
            ),
            'priorities' => array_map(
                fn (NotificationPriority $p) => ['value' => $p->value, 'label' => $p->label()],
                NotificationPriority::cases(),
            ),
            'statuses' => array_map(
                fn (NotificationReadStatus $s) => ['value' => $s->value, 'label' => $s->label()],
                NotificationReadStatus::cases(),
            ),
            'preferences' => $prefs ? [
                'commercial_alerts' => $prefs->commercial_alerts,
                'production_alerts' => $prefs->production_alerts,
                'accounting_alerts' => $prefs->accounting_alerts,
                'hr_alerts' => $prefs->hr_alerts,
                'system_alerts' => $prefs->system_alerts,
            ] : null,
            'recipientId' => $actor->id,
        ];

        return view('admin.communications.notifications.index', compact(
            'notifications',
            'summary',
            'filters',
            'prefs',
            'users',
            'bootstrap',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ErpNotification::class);

        $validated = $request->validate([
            'recipient_user_id' => ['required', 'exists:users,id'],
            'type' => ['required', Rule::enum(NotificationType::class)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['nullable', Rule::enum(NotificationPriority::class)],
            'action_url' => ['nullable', 'string', 'max:500'],
            'required_permission' => ['nullable', 'string', 'max:100'],
        ]);

        $companyId = $this->requireCompanyId();

        $notification = $this->notifications->create([
            ...$validated,
            'company_id' => $companyId,
            'created_by' => $request->user()->id,
        ]);

        if ($notification === null) {
            return response()->json(['message' => __('Notification was not delivered (preferences or permissions).')], 422);
        }

        return response()->json([
            'notification' => $this->notifications->toPayload($notification),
        ], 201);
    }

    public function markRead(Request $request, ErpNotification $notification): JsonResponse
    {
        $this->authorize('manage', $notification);
        $this->notifications->markRead($notification, $request->user());

        return response()->json(['ok' => true]);
    }

    public function dismiss(Request $request, ErpNotification $notification): JsonResponse
    {
        $this->authorize('manage', $notification);
        $this->notifications->dismiss($notification, $request->user());

        return response()->json(['ok' => true]);
    }

    public function archive(Request $request, ErpNotification $notification): JsonResponse
    {
        $this->authorize('manage', $notification);
        $this->notifications->archive($notification, $request->user());

        return response()->json(['ok' => true]);
    }

    public function bulkRead(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ErpNotification::class);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        $count = $this->notifications->bulkMarkRead($request->user(), $request->input('ids', []));

        return response()->json(['marked' => $count]);
    }

    public function bulkDismiss(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ErpNotification::class);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        $count = $this->notifications->bulkDismiss($request->user(), $request->input('ids', []));

        return response()->json(['dismissed' => $count]);
    }
}
