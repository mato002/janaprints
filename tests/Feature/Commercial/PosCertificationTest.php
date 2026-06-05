<?php

namespace Tests\Feature\Commercial;

use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\PosCertificationScope;
use App\Support\Commercial\PosCertificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_certification_fails_with_held_sale_in_open_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.certification.view']);

        $session = $this->openSession($company, $branch, $user);

        PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-CERT-HELD-001',
            'sale_date' => today(),
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100,
            'amount_paid' => 0,
            'balance_due' => 100,
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
        ]);

        $result = $this->certify($company, $branch);

        $this->assertFalse($result['passed']);
        $this->assertSame('FAIL', $result['verdict']);
        $this->assertLessThan(100, $result['score']);

        $sessionTruth = collect($result['domains'])->firstWhere('key', 'session_truth');
        $this->assertFalse($sessionTruth['passed']);
    }

    public function test_certification_fails_with_draft_sale_in_open_session(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.certification.view']);

        $session = $this->openSession($company, $branch, $user);

        PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-CERT-DRAFT-001',
            'sale_date' => today(),
            'subtotal' => 50,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50,
            'amount_paid' => 0,
            'balance_due' => 50,
            'status' => PosSaleStatus::Draft,
            'is_walk_in' => true,
        ]);

        $result = $this->certify($company, $branch);

        $this->assertFalse($result['passed']);
        $sessionTruth = collect($result['domains'])->firstWhere('key', 'session_truth');
        $this->assertFalse($sessionTruth['passed']);
    }

    public function test_certification_passes_after_resolution(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.certification.view']);

        $session = $this->openSession($company, $branch, $user);

        $sale = PosSale::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'pos_session_id' => $session->id,
            'sale_number' => 'POS-CERT-RESOLVE-001',
            'sale_date' => today(),
            'subtotal' => 80,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 80,
            'amount_paid' => 0,
            'balance_due' => 80,
            'status' => PosSaleStatus::Held,
            'is_walk_in' => true,
        ]);

        $this->assertFalse($this->certify($company, $branch)['passed']);

        $sale->update(['status' => PosSaleStatus::Cancelled, 'balance_due' => 0]);

        $result = $this->certify($company, $branch);

        $this->assertTrue($result['passed']);
        $this->assertSame('PASS', $result['verdict']);
        $this->assertSame(100, $result['score']);
    }

    public function test_certification_page_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['pos.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.certification.index'))
            ->assertForbidden();
    }

    public function test_certification_page_renders_for_authorized_user(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.pos.certification.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.pos.certification.index'))
            ->assertOk()
            ->assertSee('POS Operational Certification', false)
            ->assertSee('Inventory Truth', false)
            ->assertSee('Certification Score', false);
    }

    protected function certify(Company $company, Branch $branch): array
    {
        return app(PosCertificationService::class)->certify(new PosCertificationScope(
            companyId: $company->id,
            branchId: $branch->id,
            fromDate: today()->subDays(6),
            toDate: today(),
        ));
    }

    protected function openSession(Company $company, Branch $branch, User $user): PosSession
    {
        return PosSession::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'session_number' => 'SES-CERT-'.uniqid(),
            'opening_float' => 0,
            'opening_cash' => 0,
            'status' => PosSessionStatus::Open,
            'opened_at' => now(),
            'opened_by' => $user->id,
        ]);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
