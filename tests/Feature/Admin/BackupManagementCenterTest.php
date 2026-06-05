<?php

namespace Tests\Feature\Admin;

use App\Enums\BackupStatus;
use App\Enums\BackupType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Operations\SystemBackup;
use App\Models\User;
use App\Services\Operations\BackupManagementService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupManagementCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/backups'));
        parent::tearDown();
    }

    public function test_backup_dashboard_renders_for_authorized_admin(): void
    {
        $admin = $this->companyAdmin();
        $this->seedBackupFile('database', 'jana-db.sql', 'database dump');

        $this->actingAs($admin)
            ->get(route('admin.operations.backups.index'))
            ->assertOk()
            ->assertSee(__('Backups'))
            ->assertSee('jana-db.sql')
            ->assertSee(__('Database'));
    }

    public function test_sync_catalog_registers_backup_artifacts(): void
    {
        $this->seedBackupFile('database', 'jana-db.sql', 'database dump');
        $this->seedBackupFile('files', 'uploads.tar.gz', 'file archive');
        $this->seedBackupFile('storage', 'storage.zip', 'storage archive');

        $count = app(BackupManagementService::class)->syncCatalog();

        $this->assertSame(3, $count);
        $this->assertDatabaseHas('system_backups', [
            'name' => 'jana-db.sql',
            'type' => BackupType::Database->value,
        ]);
        $this->assertDatabaseHas('system_backups', [
            'name' => 'uploads.tar.gz',
            'type' => BackupType::File->value,
        ]);
    }

    public function test_download_requires_download_permission(): void
    {
        $viewer = $this->backupViewer();
        $backup = $this->registerBackup('database', 'secure.sql', 'secret');

        $this->actingAs($viewer)
            ->get(route('admin.operations.backups.download', $backup))
            ->assertForbidden();
    }

    public function test_authorized_admin_can_download_backup(): void
    {
        $admin = $this->companyAdmin();
        $backup = $this->registerBackup('database', 'secure.sql', 'secret');

        $this->actingAs($admin)
            ->get(route('admin.operations.backups.download', $backup))
            ->assertOk()
            ->assertHeader('content-type', 'application/octet-stream');
    }

    public function test_verify_backup_marks_record_as_verified(): void
    {
        $admin = $this->companyAdmin();
        $backup = $this->registerBackup('database', 'verify-me.sql', 'database dump');

        $this->actingAs($admin)
            ->post(route('admin.operations.backups.verify', $backup))
            ->assertRedirect(route('admin.operations.backups.index'))
            ->assertSessionHas('success');

        $backup->refresh();
        $this->assertSame(BackupStatus::Verified, $backup->status);
        $this->assertNotNull($backup->checksum_sha256);
    }

    public function test_restore_readiness_check_returns_report(): void
    {
        $admin = $this->companyAdmin();
        $backup = $this->registerBackup('database', 'ready.sql', 'database dump');
        app(BackupManagementService::class)->verify($backup);

        $this->actingAs($admin)
            ->getJson(route('admin.operations.backups.readiness', $backup))
            ->assertOk()
            ->assertJsonStructure(['ready', 'summary', 'checks', 'checked_at'])
            ->assertJsonPath('ready', true);
    }

    public function test_delete_expired_removes_old_backups(): void
    {
        $admin = $this->companyAdmin();
        $path = $this->backupPath('database', 'expired.sql');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'old backup');

        $backup = SystemBackup::query()->create([
            'name' => 'expired.sql',
            'type' => BackupType::Database,
            'relative_path' => 'database/expired.sql',
            'size_bytes' => strlen('old backup'),
            'status' => BackupStatus::Expired,
            'backup_created_at' => now()->subDays(60),
            'retention_until' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.operations.backups.delete-expired'))
            ->assertRedirect(route('admin.operations.backups.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('system_backups', ['id' => $backup->id]);
        $this->assertFileDoesNotExist($path);
    }

    public function test_permission_enforcement_blocks_view_without_rights(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.operations.backups.index'))
            ->assertForbidden();
    }

    public function test_system_operations_section_links_to_backups(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'system-operations']))
            ->assertOk()
            ->assertSee(route('admin.operations.backups.index'), false)
            ->assertSee(__('Backups'));
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function backupViewer(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');
        $user->givePermissionTo('operations.backups.view');

        return $user;
    }

    protected function seedBackupFile(string $type, string $name, string $contents): string
    {
        $path = $this->backupPath($type, $name);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        return $path;
    }

    protected function registerBackup(string $type, string $name, string $contents): SystemBackup
    {
        $this->seedBackupFile($type, $name, $contents);
        app(BackupManagementService::class)->syncCatalog();

        return SystemBackup::query()->where('name', $name)->firstOrFail();
    }

    protected function backupPath(string $type, string $name): string
    {
        return storage_path('app/backups/'.$type.'/'.$name);
    }
}
