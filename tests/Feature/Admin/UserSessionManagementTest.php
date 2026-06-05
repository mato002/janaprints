<?php

namespace Tests\Feature\Admin;

use App\Enums\LoginAttemptFailureReason;
use App\Enums\UserSessionStatus;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSessionRecord;
use App\Services\Security\UserSessionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserSessionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_login_creates_session_record(): void
    {
        $user = $this->companyAdmin();

        $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('user_session_records', [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'status' => UserSessionStatus::Active->value,
        ]);
    }

    public function test_admin_can_view_sessions_grid(): void
    {
        $admin = $this->companyAdmin();
        $this->seedSession($admin);

        $this->actingAs($admin)
            ->get(route('admin.security.sessions.index'))
            ->assertOk()
            ->assertSee(__('User Sessions'))
            ->assertSee($admin->email);
    }

    public function test_terminate_session_revokes_active_record(): void
    {
        $admin = $this->companyAdmin();
        $record = $this->seedSession($admin);

        $this->actingAs($admin)
            ->post(route('admin.security.sessions.terminate', $record))
            ->assertRedirect();

        $record->refresh();
        $this->assertSame(UserSessionStatus::Revoked, $record->status);
        $this->assertDatabaseMissing('sessions', ['id' => $record->laravel_session_id]);
    }

    public function test_force_logout_terminates_all_user_sessions(): void
    {
        $admin = $this->companyAdmin();
        $target = User::factory()->create([
            'company_id' => $admin->company_id,
            'default_branch_id' => $admin->default_branch_id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $target->assignRole('Viewer');

        $first = $this->seedSession($target, 'session-a');
        $second = $this->seedSession($target, 'session-b');

        $this->actingAs($admin)
            ->post(route('admin.security.sessions.force-logout', $target))
            ->assertRedirect();

        $this->assertSame(UserSessionStatus::Revoked, $first->fresh()->status);
        $this->assertSame(UserSessionStatus::Revoked, $second->fresh()->status);
    }

    public function test_permission_enforcement_blocks_terminate_without_rights(): void
    {
        $viewer = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->assignRole('Viewer');

        $record = $this->seedSession($viewer);

        $this->actingAs($viewer)
            ->post(route('admin.security.sessions.terminate', $record))
            ->assertForbidden();
    }

    public function test_my_sessions_page_lists_current_and_other_devices(): void
    {
        $user = $this->companyAdmin();
        $current = $this->seedSession($user, 'current-session');
        $other = $this->seedSession($user, 'other-session');

        $this->actingAs($user)
            ->withSession(['_token' => 'test'])
            ->get(route('profile.sessions.index'))
            ->assertOk()
            ->assertSee(__('My Sessions'))
            ->assertSee($current->browser)
            ->assertSee($other->browser);
    }

    public function test_my_sessions_can_logout_other_devices(): void
    {
        $user = $this->companyAdmin();
        $this->seedSession($user, 'current-session');
        $other = $this->seedSession($user, 'other-session');

        $this->actingAs($user)
            ->post(route('profile.sessions.destroy-others'))
            ->assertRedirect();

        $this->assertSame(UserSessionStatus::Revoked, $other->fresh()->status);
    }

    public function test_concurrent_sessions_metric_counts_users_with_multiple_active_sessions(): void
    {
        $user = $this->companyAdmin();
        $this->seedSession($user, 'one');
        $this->seedSession($user, 'two');

        $metrics = app(UserSessionService::class)->dashboardMetrics();

        $this->assertSame(2, $metrics['active_sessions']);
        $this->assertSame(1, $metrics['concurrent_sessions']);
    }

    public function test_failed_login_attempt_is_captured(): void
    {
        $user = $this->companyAdmin();

        $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'email' => $user->email,
            'failure_reason' => LoginAttemptFailureReason::InvalidCredentials->value,
        ]);
    }

    public function test_audit_capture_on_session_revoke(): void
    {
        $admin = $this->companyAdmin();
        $record = $this->seedSession($admin);

        $this->actingAs($admin)
            ->post(route('admin.security.sessions.terminate', $record));

        $this->assertTrue(
            ActivityLog::query()
                ->where('action', 'session_revoked')
                ->where('user_id', $admin->id)
                ->exists()
        );
    }

    public function test_security_access_workspace_links_to_user_sessions(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'security-access']))
            ->assertOk()
            ->assertSee(route('admin.security.sessions.index'), false)
            ->assertSee(__('User Sessions'));
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function seedSession(User $user, ?string $sessionId = null): UserSessionRecord
    {
        $sessionId ??= 'sess-'.uniqid();

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->getTimestamp(),
        ]);

        return UserSessionRecord::query()->create([
            'laravel_session_id' => $sessionId,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'role_snapshot' => $user->getRoleNames()->first(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            'device' => 'Desktop',
            'browser' => 'Chrome',
            'platform' => 'Windows 10/11',
            'status' => UserSessionStatus::Active,
            'login_at' => now()->subHour(),
            'last_activity_at' => now(),
        ]);
    }
}
