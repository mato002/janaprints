<?php

namespace Tests\Feature\Commercial;

use App\Enums\CommercialTicketChannel;
use App\Enums\CommercialTicketCommentVisibility;
use App\Enums\CommercialTicketPriority;
use App\Enums\CommercialTicketStatus;
use App\Models\Branch;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialSupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_ticket_create_assign_comment_and_resolve(): void
    {
        [$company, $branch, $user, $assignee] = $this->tenantUsers();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)->post(route('admin.commercial.support-tickets.store'), [
            'subject' => 'Need invoice copy',
            'description' => 'Please resend last month invoice.',
            'channel' => CommercialTicketChannel::Email->value,
            'priority' => CommercialTicketPriority::Medium->value,
        ])->assertRedirect();

        $ticket = CommercialSupportTicket::query()->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(CommercialTicketStatus::Open, $ticket->status);

        $this->actingAs($user)->post(route('admin.commercial.support-tickets.assign', $ticket), [
            'assigned_to' => $assignee->id,
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(CommercialTicketStatus::Assigned, $ticket->status);

        $this->actingAs($user)->post(route('admin.commercial.support-tickets.comment', $ticket), [
            'comment' => 'Invoice resent to customer.',
            'visibility' => CommercialTicketCommentVisibility::Internal->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('commercial_ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.commercial.support-tickets.resolve', $ticket))->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(CommercialTicketStatus::Resolved, $ticket->status);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: User}
     */
    protected function tenantUsers(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $assignee = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions([
            'commercial.tickets.view', 'commercial.tickets.create', 'commercial.tickets.edit',
            'commercial.tickets.assign', 'commercial.tickets.resolve',
        ]);
        $user->assignRole('Sales');
        $assignee->assignRole('Sales');

        return [$company, $branch, $user, $assignee];
    }
}
