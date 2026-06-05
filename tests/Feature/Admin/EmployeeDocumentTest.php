<?php

namespace Tests\Feature\Admin;

use App\Enums\EmployeeDocumentCategory;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\User;
use App\Support\Hr\EmployeeDocumentService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_dashboard_renders_for_hr_user(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.documents.dashboard'))
            ->assertOk()
            ->assertSee(__('Document Center'));
    }

    public function test_upload_creates_document_with_version(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $file = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

        $this->actingAs($hr)
            ->post(route('admin.hr.documents.store'), [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::Contract->value,
                'title' => 'Employment Contract 2026',
                'expires_at' => now()->addYear()->toDateString(),
                'file' => $file,
            ])
            ->assertRedirect();

        $document = EmployeeDocument::query()->first();
        $this->assertNotNull($document);
        $this->assertSame(1, $document->current_version);
        $this->assertDatabaseHas('employee_document_versions', [
            'employee_document_id' => $document->id,
            'version_number' => 1,
            'original_name' => 'contract.pdf',
        ]);
        Storage::disk('local')->assertExists($document->currentVersion()->path);
    }

    public function test_download_returns_file(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $service = app(EmployeeDocumentService::class);

        $document = $service->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::IdCopy->value,
                'title' => 'National ID',
            ],
            UploadedFile::fake()->create('id.pdf', 50, 'application/pdf'),
            $hr,
        );

        $this->actingAs($hr)
            ->get(route('admin.hr.documents.download', $document))
            ->assertOk();
    }

    public function test_uploading_new_version_increments_version_number(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $service = app(EmployeeDocumentService::class);

        $document = $service->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::Certificate->value,
                'title' => 'Safety Certificate',
            ],
            UploadedFile::fake()->create('cert-v1.pdf', 50, 'application/pdf'),
            $hr,
        );

        $service->uploadVersion(
            $document->fresh(),
            UploadedFile::fake()->create('cert-v2.pdf', 60, 'application/pdf'),
            $hr,
            'Renewed certificate',
        );

        $document->refresh();
        $this->assertSame(2, $document->current_version);
        $this->assertCount(2, $document->versions);
    }

    public function test_expiry_alerts_surface_on_dashboard(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $service = app(EmployeeDocumentService::class);

        $service->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::Contract->value,
                'title' => 'Expiring Contract',
                'expires_at' => now()->addDays(10)->toDateString(),
            ],
            UploadedFile::fake()->create('contract.pdf', 50, 'application/pdf'),
            $hr,
        );

        $this->actingAs($hr)
            ->get(route('admin.hr.documents.dashboard'))
            ->assertOk()
            ->assertSee('Expiring Contract')
            ->assertSee(__('Renewal due'));
    }

    public function test_viewer_cannot_access_documents(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $this->actingAs($viewer)
            ->get(route('admin.hr.documents.dashboard'))
            ->assertForbidden();
    }

    public function test_delete_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $employee = $this->testEmployee();
        $hr = $this->hrUser();

        $document = app(EmployeeDocumentService::class)->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::Cv->value,
                'title' => 'Employee CV',
            ],
            UploadedFile::fake()->create('cv.pdf', 40, 'application/pdf'),
            $hr,
        );

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Document Viewer', 'web');
        Permission::findOrCreate('hr.documents.view', 'web');
        Permission::findOrCreate('hr.documents.upload', 'web');
        $role->syncPermissions(['hr.documents.view', 'hr.documents.upload']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->delete(route('admin.hr.documents.destroy', $document))
            ->assertForbidden();
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }

    protected function testEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-DOC-001',
            'first_name' => 'Document',
            'last_name' => 'Staff',
            'email' => 'document.staff@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
