<?php

namespace App\Support\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Models\Crm\CustomerPrintSpecification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPrintSpecificationLifecycleService
{
    public function assertEditable(CustomerPrintSpecification $spec): void
    {
        if ($spec->status->isReadOnly()) {
            throw ValidationException::withMessages([
                'status' => __('Archived print specifications are read-only.'),
            ]);
        }
    }

    public function transition(
        CustomerPrintSpecification $spec,
        CustomerPrintSpecificationStatus $status,
        int $userId,
    ): CustomerPrintSpecification {
        return DB::transaction(function () use ($spec, $status, $userId) {
            $current = $spec->status;

            if ($current === $status) {
                return $spec;
            }

            if ($current->isReadOnly()) {
                throw ValidationException::withMessages([
                    'status' => __('Archived print specifications cannot change status.'),
                ]);
            }

            if (! in_array($status, $current->allowedTransitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => __('Cannot transition from :from to :to.', [
                        'from' => $current->label(),
                        'to' => $status->label(),
                    ]),
                ]);
            }

            if ($status === CustomerPrintSpecificationStatus::Active) {
                app(CustomerPrintSpecificationService::class)->assertCanActivate($spec);
            }

            $spec->update([
                'status' => $status,
                'updated_by' => $userId,
            ]);

            return $spec->fresh(['inventoryItem', 'activeArtworkVersion']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertSafeUpdate(CustomerPrintSpecification $spec, array $data): void
    {
        $this->assertEditable($spec);

        if (! $spec->hasOperationalUsage()) {
            return;
        }

        if (array_key_exists('inventory_item_id', $data)) {
            $incoming = filled($data['inventory_item_id']) ? (int) $data['inventory_item_id'] : null;
            $current = $spec->inventory_item_id ? (int) $spec->inventory_item_id : null;

            if ($incoming !== $current) {
                throw ValidationException::withMessages([
                    'inventory_item_id' => __('Product link cannot change after this specification has been used in orders or production.'),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    public function liveReferenceWarnings(CustomerPrintSpecification $spec): array
    {
        if (! $spec->hasOperationalUsage()) {
            return [];
        }

        return [
            __('Existing sales orders, job cards, and invoices keep their own frozen snapshots. Changes here apply only to future orders.'),
        ];
    }
}
