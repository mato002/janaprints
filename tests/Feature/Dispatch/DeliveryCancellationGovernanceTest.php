<?php

namespace Tests\Feature\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Services\Dispatch\DeliveryNoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DeliveryCancellationGovernanceTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_draft_can_cancel(): void
    {
        [$note, , $user] = $this->prepareDraftNoteWithFg();

        app(DeliveryNoteService::class)->cancel($note, 'Changed plan');

        $this->assertSame(DeliveryNoteStatus::Cancelled, $note->fresh()->status);
    }

    public function test_dispatched_cannot_cancel(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();

        $this->expectException(ValidationException::class);

        try {
            app(DeliveryNoteService::class)->cancel($note->fresh(), 'Too late');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
            $this->assertStringContainsString(
                'Dispatched delivery notes cannot be cancelled',
                implode(' ', $exception->errors()['status']),
            );

            throw $exception;
        }
    }

    public function test_delivered_cannot_cancel(): void
    {
        [$note, , $user] = $this->readyDispatchedDeliveryNote();
        app(DeliveryNoteService::class)->deliver($note, $user->id);

        $this->expectException(ValidationException::class);
        app(DeliveryNoteService::class)->cancel($note->fresh(), 'Attempt after delivery');
    }

    public function test_dispatched_status_can_cancel_returns_false(): void
    {
        $this->assertFalse(DeliveryNoteStatus::Dispatched->canCancel());
    }
}
