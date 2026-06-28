<?php

namespace Tests\Feature\Client;

use App\Enums\InboxConversationStatus;
use App\Models\Branch;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Communications\Inbox\InboxMessageService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientPortalInboxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function clientUser(?Customer $customer = null): User
    {
        $customer ??= Customer::factory()->create();

        return User::factory()->create([
            'company_id' => $customer->company_id,
            'default_branch_id' => $customer->branch_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);
    }

    protected function actingAsClient(User $user)
    {
        return $this->withSession(['auth_context' => 'client'])->actingAs($user);
    }

    protected function seedConversationForCustomer(Customer $customer): CommunicationConversation
    {
        return CommunicationConversation::query()->create([
            'company_id' => $customer->company_id,
            'branch_id' => $customer->branch_id,
            'conversation_code' => 'INB-CLIENT-001',
            'conversation_type' => 'customer',
            'status' => InboxConversationStatus::Open,
            'customer_id' => $customer->id,
            'display_name' => $customer->company_name,
            'started_at' => now(),
            'last_activity_at' => now(),
            'unread_count' => 0,
        ]);
    }

    public function test_client_communications_page_renders_chat(): void
    {
        $user = $this->clientUser();

        $this->actingAsClient($user)
            ->get(route('client.communications.index'))
            ->assertOk()
            ->assertSee(__('Jana Prints team'), false)
            ->assertSee(__('Write a message…'), false);
    }

    public function test_client_can_send_message_and_admin_sees_unread(): void
    {
        $user = $this->clientUser();
        $conversation = $this->seedConversationForCustomer($user->customer);

        $this->actingAsClient($user)
            ->post(route('client.communications.messages.store'), [
                'body' => 'Hello from the client portal',
            ])
            ->assertRedirect(route('client.communications.index'));

        $this->assertDatabaseHas('communication_conversation_messages', [
            'communication_conversation_id' => $conversation->id,
            'direction' => 'incoming',
            'body' => 'Hello from the client portal',
        ]);

        $this->assertSame(1, $conversation->fresh()->unread_count);
    }

    public function test_staff_reply_marks_client_unread_until_opened(): void
    {
        $user = $this->clientUser();
        $conversation = $this->seedConversationForCustomer($user->customer);

        $staff = User::factory()->create([
            'company_id' => $user->company_id,
            'default_branch_id' => $user->default_branch_id,
        ]);

        app(InboxMessageService::class)->reply(
            $conversation,
            'We received your message',
            $staff->id,
        );

        $this->actingAsClient($user)
            ->getJson(route('client.communications.unread'))
            ->assertOk()
            ->assertJson(['unread' => 1]);

        $this->actingAsClient($user)
            ->get(route('client.communications.index'))
            ->assertOk();

        $this->actingAsClient($user)
            ->getJson(route('client.communications.unread'))
            ->assertOk()
            ->assertJson(['unread' => 0]);

        $this->assertNotNull(
            CommunicationConversationMessage::query()
                ->where('communication_conversation_id', $conversation->id)
                ->where('direction', 'outgoing')
                ->value('read_at')
        );
    }

    public function test_client_can_upload_attachment(): void
    {
        Storage::fake('public');
        $user = $this->clientUser();
        $conversation = $this->seedConversationForCustomer($user->customer);

        $this->actingAsClient($user)
            ->post(route('client.communications.attachments.store'), [
                'file' => UploadedFile::fake()->image('proof.jpg'),
                'caption' => 'Please review this artwork',
            ])
            ->assertRedirect(route('client.communications.index'));

        $this->assertDatabaseHas('communication_conversation_attachments', [
            'communication_conversation_id' => $conversation->id,
            'label' => 'proof.jpg',
        ]);

        $this->assertSame(1, $conversation->fresh()->unread_count);
    }

    public function test_admin_inbox_lists_client_conversation_with_unread_badge(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Portal Chat Co',
        ]);
        $client = $this->clientUser($customer);
        $conversation = $this->seedConversationForCustomer($customer);

        $admin = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
        ]);
        Permission::findOrCreate('communications.inbox.view');
        $admin->givePermissionTo('communications.inbox.view');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAsClient($client)
            ->post(route('client.communications.messages.store'), [
                'body' => 'Need an update on my order',
            ]);

        $this->actingAs($admin)
            ->get(route('admin.communications.inbox.index', [
                'embedded' => '1',
                'conversation' => $conversation->id,
            ]), ['Turbo-Frame' => 'module-workspace-content'])
            ->assertOk()
            ->assertSee('Need an update on my order', false);
    }

    public function test_client_feed_returns_messages_without_full_reload(): void
    {
        $user = $this->clientUser();
        $conversation = $this->seedConversationForCustomer($user->customer);

        app(InboxMessageService::class)->receiveFromCustomer(
            $conversation,
            'Live update test',
            $user->id,
        );

        $this->actingAsClient($user)
            ->getJson(route('client.communications.feed'))
            ->assertOk()
            ->assertJsonStructure(['fingerprint', 'html', 'unread'])
            ->assertSee('Live update test', false);
    }

    public function test_quote_parser_splits_quoted_reply(): void
    {
        $parsed = \App\Support\Client\ClientChatMessagePresenter::splitQuote("> hello\n\nthat");

        $this->assertSame('hello', $parsed['quoted']);
        $this->assertNull($parsed['quoted_author']);
        $this->assertSame('that', $parsed['body']);

        $singleGap = \App\Support\Client\ClientChatMessagePresenter::splitQuote("> hello\nthat");
        $this->assertSame('hello', $singleGap['quoted']);
        $this->assertSame('that', $singleGap['body']);

        $withAuthor = \App\Support\Client\ClientChatMessagePresenter::splitQuote("> [Jana Prints]\n> hello\n\nthat");
        $this->assertSame('Jana Prints', $withAuthor['quoted_author']);
        $this->assertSame('hello', $withAuthor['quoted']);
        $this->assertSame('that', $withAuthor['body']);
    }
}
