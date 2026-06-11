<?php

namespace Tests\Support;

use App\Services\Website\WebsiteSettingsService;

trait BuildsWebsiteSettingsPayload
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function footerContactPayload(array $overrides = []): array
    {
        $service = app(WebsiteSettingsService::class);
        $schema = $service->schemaForGroups(['footer', 'contact', 'seo'], 'footer-contact');
        $payload = [];

        foreach ($schema as $key => $meta) {
            $field = str_replace('.', '_', $key);
            $value = $service->get($key);

            if ($meta['type'] === 'json') {
                $payload[$field] = in_array($key, ['footer.nav', 'footer.trust_badges'], true)
                    ? (is_array($value) ? $value : [])
                    : json_encode($value ?? [], JSON_THROW_ON_ERROR);
            } elseif ($meta['type'] === 'boolean') {
                $payload[$field] = $value ? '1' : '0';
            } else {
                $payload[$field] = $value ?? '';

                if (
                    ($meta['optional'] ?? false)
                    && ($meta['type'] ?? '') === 'url'
                    && ($payload[$field] === '#' || ! filter_var((string) $payload[$field], FILTER_VALIDATE_URL))
                ) {
                    $payload[$field] = '';
                }
            }
        }

        return array_merge($payload, $overrides);
    }
}
