<?php

namespace Tests\Feature\Communications;

use App\Enums\InboxConversationStatus;
use App\Models\Branch;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InboxCom8aTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_inbox_index_renders_three_panel_context(): void
    {
        [$user, $conversation] = $this->seedConversation();

        $this->actingAs($user)
            ->get(route('admin.communications.inbox.index', [
                'conversation' => $conversation->id,
                'embedded' => '1',
            ]), ['Turbo-Frame' => 'module-workspace-content'])
            ->assertOk()
            ->assertSee(__('Message'))
            ->assertSee(__('Customer profile (360)'))
            ->assertSee(__('Insights'))
            ->assertSee(__('New conversation'));
    }

    public function test_embedded_inbox_conversation_links_target_workspace_frame(): void
    {
        [$user, $conversation] = $this->seedConversation();

        $this->actingAs($user)
            ->get(route('admin.communications.inbox.index', ['embedded' => '1']), [
                'Turbo-Frame' => 'module-workspace-content',
            ])
            ->assertOk()
            ->assertSee('data-turbo-frame="module-workspace-content"', false)
            ->assertSee('embedded=1', false)
            ->assertSee('conversation='.$conversation->id, false);

        $this->actingAs($user)
            ->get(route('admin.communications.inbox.index', [
                'embedded' => '1',
                'conversation' => $conversation->id,
            ]), [
                'Turbo-Frame' => 'module-workspace-content',
            ])
            ->assertOk()
            ->assertSee('shared-inbox__thread', false)
            ->assertDontSee(__('Select a conversation'));
    }

    public function test_executive_view_requires_executive_permission(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);
        Permission::findOrCreate('communications.inbox.view');
        $user->givePermissionTo('communications.inbox.view');
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.communications.inbox.executive'))
            ->assertForbidden();
    }

    public function test_start_conversation_from_customer_picker(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);
        Permission::findOrCreate('communications.inbox.view');
        $user->givePermissionTo('communications.inbox.view');

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Picker Test Co',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.communications.inbox.start'), ['customer_id' => $customer->id])
            ->assertRedirect();

        $this->assertDatabaseHas('communication_conversations', [
            'customer_id' => $customer->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_inbox_shows_start_conversation_picker(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);
        Permission::findOrCreate('communications.inbox.view');
        $user->givePermissionTo('communications.inbox.view');
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.communications.inbox.index', ['embedded' => '1']), [
                'Turbo-Frame' => 'module-workspace-content',
            ])
            ->assertOk()
            ->assertSee(__('New conversation'))
            ->assertSee(__('Open'));
    }

    public function test_status_workflow_updates_conversation(): void
    {
        [$user, $conversation] = $this->seedConversation();

        $this->actingAs($user)
            ->post(route('admin.communications.inbox.status', $conversation), [
                'status' => InboxConversationStatus::WaitingCustomer->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('communication_conversations', [
            'id' => $conversation->id,
            'status' => InboxConversationStatus::WaitingCustomer->value,
        ]);
    }

    /**
     * @return array{0: User, 1: CommunicationConversation}
     */
    protected function seedConversation(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id, 'default_branch_id' => $branch->id]);
        foreach ([
            'communications.inbox.view', 'communications.inbox.reply', 'communications.inbox.close',
            'communications.inbox.notes', 'communications.inbox.assign',
        ] as $perm) {
            Permission::findOrCreate($perm);
            $user->givePermissionTo($perm);
        }

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $conversation = CommunicationConversation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'conversation_code' => 'INB-TEST-001',
            'conversation_type' => 'customer',
            'status' => InboxConversationStatus::Open,
            'customer_id' => $customer->id,
            'display_name' => $customer->name,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$user, $conversation];
    }
}
