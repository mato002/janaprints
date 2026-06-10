<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Receipt = 'receipt';
    case Issue = 'issue';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case ProductionConsumption = 'production_consumption';
    case WipTransfer = 'wip_transfer';
    case ProductionOutput = 'production_output';
    case FinishedGoodsReceipt = 'finished_goods_receipt';
    case DispatchToTransit = 'dispatch_to_transit';
    case DeliveryCogs = 'delivery_cogs';
    case QuarantineTransfer = 'quarantine_transfer';
}
