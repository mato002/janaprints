<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Enums\NotificationReadStatus;
use App\Http\Controllers\Controller;
use App\Models\Communications\ErpNotification;
use App\Support\Communications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationBellController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function panel(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ErpNotification::class);

        $user = $request->user();
        $companyId = tenant()->companyId() ?? $user->company_id;

        $items = $this->notifications
            ->recentForUser($user, 10, $companyId)
            ->map(fn (ErpNotification $n) => $this->notifications->toPayload($n));

        return response()->json([
            'unread_count' => $this->notifications->unreadCount($user, $companyId),
            'notifications' => $items,
            'routes' => [
                'center' => route('admin.communications.notifications.index'),
                'mark_all_read' => route('admin.communications.notifications.mark-all-read'),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ErpNotification::class);

        $user = $request->user();
        $companyId = tenant()->companyId() ?? $user->company_id;

        return response()->json([
            'unread_count' => $this->notifications->unreadCount($user, $companyId),
        ]);
    }

    public function markRead(Request $request, ErpNotification $notification): JsonResponse
    {
        $this->authorize('manage', $notification);

        $this->notifications->markRead($notification, $request->user());

        return response()->json([
            'notification' => $this->notifications->toPayload($notification->fresh(['readState', 'creator'])),
            'unread_count' => $this->notifications->unreadCount(
                $request->user(),
                tenant()->companyId() ?? $request->user()->company_id,
            ),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ErpNotification::class);

        $user = $request->user();
        $companyId = tenant()->companyId() ?? $user->company_id;
        $count = $this->notifications->markAllRead($user, $companyId);

        return response()->json([
            'marked' => $count,
            'unread_count' => 0,
        ]);
    }

    public function open(Request $request, ErpNotification $notification): JsonResponse
    {
        $this->authorize('view', $notification);

        if ($request->user()->can('communications.notifications.manage')
            && $notification->recipient_user_id === $request->user()->id
            && $notification->readState?->status === NotificationReadStatus::Unread) {
            $this->notifications->markRead($notification, $request->user());
        }

        return response()->json([
            'notification' => $this->notifications->toPayload($notification->fresh(['readState', 'creator'])),
            'redirect_url' => $notification->action_url,
            'unread_count' => $this->notifications->unreadCount(
                $request->user(),
                tenant()->companyId() ?? $request->user()->company_id,
            ),
        ]);
    }
}
