<?php

namespace Tests\Feature\Commercial;

use App\Enums\CommercialPriceBookStatus;
use App\Models\Branch;
use App\Models\Commercial\CommercialPriceBook;
use App\Models\Commercial\CommercialPriceBookItem;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\User;
use App\Support\Commercial\CommercialPriceBookService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialPriceBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_index_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->get(route('admin.commercial.price-books.index'))->assertForbidden();
    }

    public function test_price_book_can_be_created_and_assigned_to_customer(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.price_books.view', 'commercial.price_books.create', 'commercial.price_books.edit',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.price-books.store'), [
            'name' => 'Retail',
            'code' => 'RETAIL',
            'currency' => 'KES',
            'status' => CommercialPriceBookStatus::Active->value,
            'is_default' => true,
        ])->assertRedirect();

        $book = CommercialPriceBook::query()->where('code', 'RETAIL')->first();
        $this->assertNotNull($book);
        $this->assertTrue($book->is_default);

        $this->actingAs($user)->post(route('admin.commercial.price-books.assign-customer', $book), [
            'customer_id' => $customer->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('commercial_customer_price_books', [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'price_book_id' => $book->id,
            'status' => CommercialPriceBookStatus::Active->value,
        ]);
    }

    public function test_price_resolution_uses_price_book_and_falls_back_to_standard_cost(): void
    {
        [$company, $branch] = [Company::factory()->create(), null];
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
        $item = InventoryItem::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'standard_cost' => 80,
        ]);

        $service = app(CommercialPriceBookService::class);

        $this->assertSame(80.0, $service->resolveInventoryFallbackPrice($item, null, $company->id, $branch->id));

        $book = CommercialPriceBook::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'VIP',
            'code' => 'VIP',
            'currency' => 'KES',
            'status' => CommercialPriceBookStatus::Active,
            'is_default' => true,
        ]);

        CommercialPriceBookItem::query()->create([
            'price_book_id' => $book->id,
            'inventory_item_id' => $item->id,
            'unit_price' => 120,
            'status' => CommercialPriceBookStatus::Active,
        ]);

        $service->assignCustomerPriceBook($company->id, $customer->id, $book->id);

        $this->assertSame(120.0, $service->resolveInventoryFallbackPrice($item, $customer->id, $company->id, $branch->id));
    }

    /**
     * @param  list<string>  $permissions
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
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
