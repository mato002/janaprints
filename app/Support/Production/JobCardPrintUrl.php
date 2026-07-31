<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Facades\Route;

final class JobCardPrintUrl
{
    public static function resolve(ProductionJobCard $job, ?string $department = null): ?string
    {
        if (Route::has('admin.production.job-cards.job-sheet')) {
            return route('admin.production.job-cards.job-sheet', $job);
        }

        if (Route::has('admin.production.job-cards.floor-display')) {
            return route('admin.production.job-cards.floor-display', $job);
        }

        return null;
    }

    public static function usesJobSheet(ProductionJobCard $job, ?string $department = null): bool
    {
        return Route::has('admin.production.job-cards.job-sheet');
    }

    public static function actionLabel(ProductionJobCard $job, ?string $department = null): string
    {
        return __('Print job sheet');
    }

    public static function pathSegment(ProductionJobCard $job, ?string $department = null): string
    {
        if (Route::has('admin.production.job-cards.job-sheet')) {
            return 'job-sheet';
        }

        return 'floor-display';
    }

    public static function departmentSlugFor(ProductionJobCard $job): ?string
    {
        return match ($job->production_type?->value) {
            'offset' => 'offset',
            'digital' => 'digital',
            default => null,
        };
    }
}
