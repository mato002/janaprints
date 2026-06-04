<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\InboxConversationStatus;
use App\Enums\InboxMessageChannel;
use App\Enums\InboxMessageStatus;
use App\Enums\InboxSlaStatus;
use App\Models\Branch;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Communications\Inbox\InboxConversationService;
use App\Support\Communications\Inbox\InboxMessageService;
use Illuminate\Database\Seeder;

class InboxDemoSeeder extends Seeder
{
    /**
     * Demo customers + inbox threads for testing Shared Inbox picker and flow.
     */
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();
        if (! $company) {
            $this->command?->warn('Inbox demo skipped: run OrganizationFoundationSeeder first (company JANA).');

            return;
        }

        $branch = Branch::query()
            ->where('company_id', $company->id)
            ->where('code', 'HQ')
            ->first();

        if (! $branch) {
            $this->command?->warn('Inbox demo skipped: HQ branch not found.');

            return;
        }

        $actor = User::query()->where('company_id', $company->id)->orderBy('id')->first();
        $actorId = $actor?->id;

        $customers = [
            [
                'customer_code' => 'DEMO-INB-001',
                'company_name' => 'Nairobi Tech Solutions Ltd',
                'contact_person' => 'Grace Wanjiku',
                'phone' => '0712345001',
                'email' => 'grace@nairobitech.demo',
                'city' => 'Nairobi',
            ],
            [
                'customer_code' => 'DEMO-INB-002',
                'company_name' => 'Westlands Branding Co',
                'contact_person' => 'James Otieno',
                'phone' => '0722345002',
                'email' => 'james@westlandsbrand.demo',
                'city' => 'Nairobi',
            ],
            [
                'customer_code' => 'DEMO-INB-003',
                'company_name' => 'Mombasa Events & Prints',
                'contact_person' => 'Amina Hassan',
                'phone' => '0733345003',
                'email' => 'orders@mombasaevents.demo',
                'city' => 'Mombasa',
            ],
            [
                'customer_code' => 'DEMO-INB-004',
                'company_name' => 'Kisumu Corporate Gifts',
                'contact_person' => 'Peter Ochieng',
                'phone' => '0744345004',
                'email' => 'peter@kisumugifts.demo',
                'city' => 'Kisumu',
            ],
            [
                'customer_code' => 'DEMO-INB-005',
                'company_name' => 'Eldoret School Supplies',
                'contact_person' => 'Mary Chebet',
                'phone' => '0755345005',
                'email' => 'mary@eldoretschools.demo',
                'city' => 'Eldoret',
            ],
            [
                'customer_code' => 'DEMO-INB-006',
                'company_name' => 'Thika Industrial Labels',
                'contact_person' => 'David Kamau',
                'phone' => '0766345006',
                'email' => 'david@thikalabels.demo',
                'city' => 'Thika',
            ],
        ];

        $createdCustomers = collect();

        foreach ($customers as $row) {
            $createdCustomers->push(Customer::query()->firstOrCreate(
                ['company_id' => $company->id, 'customer_code' => $row['customer_code']],
                [
                    'branch_id' => $branch->id,
                    'customer_type' => CustomerType::Corporate,
                    'company_name' => $row['company_name'],
                    'contact_person' => $row['contact_person'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'city' => $row['city'],
                    'status' => CustomerStatus::Active,
                    'credit_limit' => 500000,
                ],
            ));
        }

        if (! $actorId) {
            $this->command?->info('Created '.count($customers).' demo customers (no user — inbox threads skipped).');
            $this->printPickerHints();

            return;
        }

        $conversations = app(InboxConversationService::class);
        $messages = app(InboxMessageService::class);

        $threads = [
            [
                'customer_code' => 'DEMO-INB-001',
                'status' => InboxConversationStatus::Open,
                'assigned_user_id' => $actorId,
                'unread_count' => 2,
                'preview' => 'Need 500 branded t-shirts by Friday',
                'customer_msgs' => [
                    'Hi, we need 500 branded t-shirts for an event on Friday. Can you quote?',
                    'Also need delivery to Westlands — is that possible?',
                ],
                'staff_reply' => 'Thanks Grace — we can quote today. Sending artwork checklist shortly.',
            ],
            [
                'customer_code' => 'DEMO-INB-002',
                'status' => InboxConversationStatus::WaitingCustomer,
                'assigned_user_id' => $actorId,
                'unread_count' => 0,
                'preview' => 'Quote QT-2026 sent — awaiting approval',
                'customer_msgs' => [
                    'Please send quote for 2000 flyers A5 double-sided.',
                ],
                'staff_reply' => 'Quote sent via email. Let us know if you need any changes.',
            ],
            [
                'customer_code' => 'DEMO-INB-003',
                'status' => InboxConversationStatus::Open,
                'assigned_user_id' => null,
                'unread_count' => 1,
                'preview' => 'Urgent: wedding programme printing',
                'customer_msgs' => [
                    'Urgent — wedding programmes needed by Saturday. 150 copies.',
                ],
                'staff_reply' => null,
            ],
        ];

        foreach ($threads as $thread) {
            $customer = $createdCustomers->firstWhere('customer_code', $thread['customer_code']);
            if (! $customer) {
                continue;
            }

            $conversation = $conversations->findOrCreateForCustomer($customer, $actorId);
            $conversation->update([
                'status' => $thread['status'],
                'assigned_user_id' => $thread['assigned_user_id'],
                'owner_user_id' => $thread['assigned_user_id'],
                'unread_count' => $thread['unread_count'],
                'last_message_preview' => $thread['preview'],
                'waiting_since' => now()->subHours(3),
                'sla_status' => InboxSlaStatus::Amber,
                'phone_number' => $customer->phone,
                'email' => $customer->email,
                'display_name' => $customer->name,
            ]);

            foreach ($thread['customer_msgs'] as $body) {
                CommunicationConversationMessage::query()->create([
                    'communication_conversation_id' => $conversation->id,
                    'body' => $body,
                    'direction' => 'incoming',
                    'company_id' => $company->id,
                    'channel' => InboxMessageChannel::WhatsApp,
                    'message_type' => 'message',
                    'status' => InboxMessageStatus::Delivered,
                    'sent_at' => now()->subHours(2),
                    'delivered_at' => now()->subHours(2),
                ]);
            }

            if ($thread['staff_reply']) {
                $messages->reply($conversation, $thread['staff_reply'], $actorId, InboxMessageChannel::InApp);
            }

            $conversations->touchActivity($conversation, $thread['preview'], 'whatsapp');
        }

        $this->command?->info('Inbox demo ready: '.count($customers).' customers, '.count($threads).' active threads.');
        $this->printPickerHints();
    }

    protected function printPickerHints(): void
    {
        $this->command?->line('');
        $this->command?->line('  Test Shared Inbox:');
        $this->command?->line('  1. Log in → Communications → Shared Inbox');
        $this->command?->line('  2. Start conversation → search "Nairobi" or "Mombasa" → Open conversation');
        $this->command?->line('  3. Or pick DEMO-INB-004 / DEMO-INB-005 (no thread yet) to test new thread');
        $this->command?->line('  4. Existing threads: Nairobi Tech, Westlands Branding, Mombasa Events');
        $this->command?->line('');
    }
}
