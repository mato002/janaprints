<?php

namespace App\Services\Security;

use App\Enums\UserSessionStatus;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSessionRecord;
use App\Support\ActivityLogger;
use App\Support\Security\UserAgentParser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserSessionService
{
    public function __construct(
        protected UserAgentParser $userAgentParser,
    ) {}

    public function recordLogin(User $user, Request $request): UserSessionRecord
    {
        $parsed = $this->userAgentParser->parse($request->userAgent());
        $now = now();

        return UserSessionRecord::query()->create([
            'laravel_session_id' => $request->session()->getId(),
            'user_id' => $user->getKey(),
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'role_snapshot' => $user->getRoleNames()->first(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'location' => null,
            'status' => UserSessionStatus::Active,
            'login_at' => $now,
            'last_activity_at' => $now,
        ]);
    }

    public function recordLogout(?string $sessionId): void
    {
        if ($sessionId === null) {
            return;
        }

        UserSessionRecord::query()
            ->where('laravel_session_id', $sessionId)
            ->where('status', UserSessionStatus::Active)
            ->update([
                'status' => UserSessionStatus::LoggedOut,
                'logged_out_at' => now(),
                'last_activity_at' => now(),
            ]);
    }

    public function syncStatuses(): void
    {
        $lifetimeMinutes = (int) config('session.lifetime', 120);
        $cutoff = now()->subMinutes($lifetimeMinutes)->getTimestamp();

        $activeRecords = UserSessionRecord::query()
            ->where('status', UserSessionStatus::Active)
            ->get(['id', 'laravel_session_id', 'last_activity_at']);

        if ($activeRecords->isEmpty()) {
            return;
        }

        $sessionIds = $activeRecords->pluck('laravel_session_id')->filter()->all();
        $liveSessions = $sessionIds === []
            ? collect()
            : DB::table('sessions')->whereIn('id', $sessionIds)->get(['id', 'last_activity'])->keyBy('id');

        foreach ($activeRecords as $record) {
            $sessionId = $record->laravel_session_id;
            $live = $sessionId ? $liveSessions->get($sessionId) : null;

            if ($live === null) {
                $record->update([
                    'status' => UserSessionStatus::LoggedOut,
                    'logged_out_at' => now(),
                ]);

                continue;
            }

            $lastActivity = Carbon::createFromTimestamp((int) $live->last_activity);

            if ($lastActivity->getTimestamp() < $cutoff) {
                $record->update([
                    'status' => UserSessionStatus::Expired,
                    'last_activity_at' => $lastActivity,
                ]);

                continue;
            }

            if ($record->last_activity_at?->ne($lastActivity)) {
                $record->update(['last_activity_at' => $lastActivity]);
            }
        }
    }

    /**
     * @return array{
     *     active_sessions: int,
     *     logged_in_users: int,
     *     failed_logins_today: int,
     *     locked_accounts_today: int,
     *     concurrent_sessions: int,
     * }
     */
    public function dashboardMetrics(): array
    {
        $this->syncStatuses();

        $activeQuery = UserSessionRecord::query()->forTenant()->where('status', UserSessionStatus::Active);

        $activeSessions = (clone $activeQuery)->count();
        $loggedInUsers = (clone $activeQuery)->distinct('user_id')->count('user_id');

        $concurrentSessions = (clone $activeQuery)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $failedTodayQuery = LoginAttempt::query()
            ->forTenant()
            ->whereDate('attempted_at', today());

        return [
            'active_sessions' => $activeSessions,
            'logged_in_users' => $loggedInUsers,
            'failed_logins_today' => (clone $failedTodayQuery)->count(),
            'locked_accounts_today' => (clone $failedTodayQuery)
                ->where('failure_reason', 'locked_out')
                ->distinct('email')
                ->count('email'),
            'concurrent_sessions' => $concurrentSessions,
        ];
    }

    public function paginate(?string $status = null, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $this->syncStatuses();

        $query = UserSessionRecord::query()
            ->forTenant()
            ->with(['user', 'company', 'branch', 'revokedByUser'])
            ->orderByDesc('last_activity_at');

        if ($status !== null && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== null && trim($search) !== '') {
            $like = '%'.trim($search).'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('ip_address', 'like', $like)
                    ->orWhere('device', 'like', $like)
                    ->orWhere('browser', 'like', $like)
                    ->orWhere('platform', 'like', $like)
                    ->orWhere('role_snapshot', 'like', $like)
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like));
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, UserSessionRecord>
     */
    public function sessionsForUser(User $user, bool $includeInactive = true): Collection
    {
        $this->syncStatuses();

        $query = UserSessionRecord::query()
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity_at');

        if (! $includeInactive) {
            $query->where('status', UserSessionStatus::Active);
        }

        return $query->get();
    }

    public function terminate(UserSessionRecord $record, User $actor, ?string $reason = null): void
    {
        if ($record->status !== UserSessionStatus::Active) {
            return;
        }

        if ($record->laravel_session_id) {
            DB::table('sessions')->where('id', $record->laravel_session_id)->delete();
        }

        $record->update([
            'status' => UserSessionStatus::Revoked,
            'revoked_at' => now(),
            'revoked_by' => $actor->getKey(),
            'revoke_reason' => $reason,
            'last_activity_at' => now(),
        ]);

        ActivityLogger::log('session_revoked', $record->user, $record->user_id, [
            'session_record_id' => $record->getKey(),
            'laravel_session_id' => $record->laravel_session_id,
            'revoked_by' => $actor->getKey(),
            'reason' => $reason,
        ]);
    }

    public function terminateAllForUser(User $target, User $actor, ?string $exceptSessionId = null, ?string $reason = null): int
    {
        $records = UserSessionRecord::query()
            ->where('user_id', $target->getKey())
            ->where('status', UserSessionStatus::Active)
            ->get();

        $terminated = 0;

        foreach ($records as $record) {
            if ($exceptSessionId !== null && $record->isCurrentSession($exceptSessionId)) {
                continue;
            }

            $this->terminate($record, $actor, $reason);
            $terminated++;
        }

        return $terminated;
    }

    public function forceLogoutUser(User $target, User $actor, ?string $reason = null): int
    {
        $count = $this->terminateAllForUser($target, $actor, null, $reason ?? __('Force logout'));

        ActivityLogger::log('force_logout', $target, $target->getKey(), [
            'terminated_sessions' => $count,
            'initiated_by' => $actor->getKey(),
        ]);

        return $count;
    }
}
