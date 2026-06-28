<?php

namespace App\Enums;

enum ProductionFloorStage: string
{
    case Waiting = 'waiting';
    case OnPress = 'on_press';
    case AtVendor = 'at_vendor';
    case Finishing = 'finishing';
    case Qc = 'qc';
    case Ready = 'ready';
    case Out = 'out';
    case OnHold = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => __('Waiting'),
            self::OnPress => __('On press'),
            self::AtVendor => __('At vendor'),
            self::Finishing => __('Finishing'),
            self::Qc => __('QC'),
            self::Ready => __('Ready'),
            self::Out => __('Out'),
            self::OnHold => __('On hold'),
        };
    }

    public static function fromJobStatus(ProductionJobCardStatus $status): self
    {
        return match ($status) {
            ProductionJobCardStatus::Draft,
            ProductionJobCardStatus::Queued => self::Waiting,
            ProductionJobCardStatus::InProduction,
            ProductionJobCardStatus::Rework => self::OnPress,
            ProductionJobCardStatus::Outsourced => self::AtVendor,
            ProductionJobCardStatus::Returned,
            ProductionJobCardStatus::Completed => self::Finishing,
            ProductionJobCardStatus::QualityCheck,
            ProductionJobCardStatus::AwaitingCustomerApproval => self::Qc,
            ProductionJobCardStatus::ReadyForDispatch => self::Ready,
            ProductionJobCardStatus::OnHold => self::OnHold,
            ProductionJobCardStatus::Cancelled => self::Waiting,
        };
    }

    /**
     * @return list<self>
     */
    public static function activeStages(): array
    {
        return [
            self::Waiting,
            self::OnPress,
            self::AtVendor,
            self::Finishing,
            self::Qc,
            self::Ready,
            self::Out,
            self::OnHold,
        ];
    }
}
