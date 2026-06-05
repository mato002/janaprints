<?php

namespace App\Support\Security;

class UserAgentParser
{
    /**
     * @return array{device: string, browser: string, platform: string}
     */
    public function parse(?string $userAgent): array
    {
        $userAgent = (string) $userAgent;

        if ($userAgent === '') {
            return [
                'device' => __('Unknown'),
                'browser' => __('Unknown'),
                'platform' => __('Unknown'),
            ];
        }

        return [
            'device' => $this->detectDevice($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
        ];
    }

    protected function detectPlatform(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows NT 10') => 'Windows 10/11',
            str_contains($userAgent, 'Windows NT') => 'Windows',
            str_contains($userAgent, 'Mac OS X'), str_contains($userAgent, 'Macintosh') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone'), str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => __('Unknown'),
        };
    }

    protected function detectBrowser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Edg/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome/') => 'Safari',
            default => __('Unknown'),
        };
    }

    protected function detectDevice(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') => 'iPad',
            str_contains($userAgent, 'iPhone') => 'iPhone',
            str_contains($userAgent, 'Android') && str_contains($userAgent, 'Mobile') => 'Android Phone',
            str_contains($userAgent, 'Android') => 'Android Tablet',
            str_contains($userAgent, 'Mobile') => __('Mobile'),
            default => __('Desktop'),
        };
    }
}
