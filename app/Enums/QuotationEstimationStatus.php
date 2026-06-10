<?php

namespace App\Enums;

enum QuotationEstimationStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case ManualReview = 'manual_review';
    case AppliedToQuotation = 'applied_to_quotation';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Processing => __('Processing'),
            self::Completed => __('Completed'),
            self::Failed => __('Failed'),
            self::ManualReview => __('Manual review'),
            self::AppliedToQuotation => __('Applied to quotation'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700',
            self::Processing => 'bg-sky-50 text-sky-800',
            self::Completed => 'bg-emerald-50 text-emerald-800',
            self::Failed => 'bg-red-50 text-red-800',
            self::ManualReview => 'bg-amber-50 text-amber-900',
            self::AppliedToQuotation => 'bg-indigo-50 text-indigo-800',
        };
    }
}
