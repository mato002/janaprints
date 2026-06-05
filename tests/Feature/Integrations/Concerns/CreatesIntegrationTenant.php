<?php

namespace Tests\Feature\Integrations\Concerns;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait CreatesIntegrationTenant
{
    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function integrationTenant(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }

    protected function actingAsTenant(User $user, Company $company, Branch $branch): static
    {
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $this->actingAs($user);
    }
}
