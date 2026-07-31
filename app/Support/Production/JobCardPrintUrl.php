<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Facades\Route;

final class JobCardPrintUrl
{
    public static function resolve(ProductionJobCard $job, ?string $department = null): ?string
    {
        $department ??= self::departmentSlugFor($job);

        if (in_array($department, ['offset', 'digital'], true)
            && Route::has('admin.production.job-cards.job-sheet')) {
            return route('admin.production.job-cards.job-sheet', $job);
        }

        if (Route::has('admin.production.job-cards.floor-display')) {
            return route('admin.production.job-cards.floor-display', $job);
        }

        return null;
    }

    public static function usesJobSheet(ProductionJobCard $job, ?string $department = null): bool
    {
        $department ??= self::departmentSlugFor($job);

        return in_array($department, ['offset', 'digital'], true);
    }

    public static function actionLabel(ProductionJobCard $job, ?string $department = null): string
    {
        return self::usesJobSheet($job, $department)
            ? __('Print job sheet')
            : __('Print job card');
    }

    public static function pathSegment(ProductionJobCard $job, ?string $department = null): string
    {
        $department ??= self::departmentSlugFor($job);

        if (in_array($department, ['offset', 'digital'], true)
            && Route::has('admin.production.job-cards.job-sheet')) {
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
