<?php

namespace App\Support\PrintingIntelligence;

class CmykAreaComposition
{
    /**
     * Normalize independent channel coverage values into an area composition totalling 100%.
     *
     * @return array{
     *     cyan: float,
     *     magenta: float,
     *     yellow: float,
     *     black: float,
     *     white: float,
     *     transparent: float,
     *     total: float,
     * }
     */
    public static function fromChannelCoverage(
        float $cyan,
        float $magenta,
        float $yellow,
        float $black,
        float $white = 0.0,
        float $transparent = 0.0,
    ): array {
        $white = max(0, min(100, $white));
        $transparent = max(0, min(100, $transparent));
        $remaining = max(0, 100 - $white - $transparent);

        $inkTotal = max(0, $cyan) + max(0, $magenta) + max(0, $yellow) + max(0, $black);

        if ($inkTotal <= 0 || $remaining <= 0) {
            $total = round($white + $transparent, 3);

            return [
                'cyan' => 0.0,
                'magenta' => 0.0,
                'yellow' => 0.0,
                'black' => 0.0,
                'white' => round($white, 3),
                'transparent' => round($transparent, 3),
                'total' => $total,
            ];
        }

        $scale = $remaining / $inkTotal;

        $composition = [
            'cyan' => round(max(0, $cyan) * $scale, 3),
            'magenta' => round(max(0, $magenta) * $scale, 3),
            'yellow' => round(max(0, $yellow) * $scale, 3),
            'black' => round(max(0, $black) * $scale, 3),
            'white' => round($white, 3),
            'transparent' => round($transparent, 3),
        ];

        $composition['total'] = round(
            $composition['cyan']
            + $composition['magenta']
            + $composition['yellow']
            + $composition['black']
            + $composition['white']
            + $composition['transparent'],
            3,
        );

        $composition = self::rebalanceToHundred($composition);

        return $composition;
    }

    /**
     * @param  array{cyan: float, magenta: float, yellow: float, black: float, white?: float, transparent?: float}  $composition
     * @return array{cyan: float, magenta: float, yellow: float, black: float, white: float, transparent: float, total: float}
     */
    public static function rebalanceToHundred(array $composition): array
    {
        $keys = ['cyan', 'magenta', 'yellow', 'black', 'white', 'transparent'];
        $sum = 0.0;

        foreach ($keys as $key) {
            $sum += (float) ($composition[$key] ?? 0);
        }

        if ($sum <= 0) {
            return [
                'cyan' => 0.0,
                'magenta' => 0.0,
                'yellow' => 0.0,
                'black' => 0.0,
                'white' => 0.0,
                'transparent' => 0.0,
                'total' => 0.0,
            ];
        }

        $delta = 100 - $sum;

        if (abs($delta) > 0.001) {
            $largestKey = collect($keys)
                ->sortByDesc(fn (string $key) => (float) ($composition[$key] ?? 0))
                ->first() ?? 'cyan';

            $composition[$largestKey] = round((float) ($composition[$largestKey] ?? 0) + $delta, 3);
        }

        $composition['white'] = round((float) ($composition['white'] ?? 0), 3);
        $composition['transparent'] = round((float) ($composition['transparent'] ?? 0), 3);
        $composition['total'] = 100.0;

        return $composition;
    }
}
