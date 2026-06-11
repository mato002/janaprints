<?php

namespace App\Support\Website;

use App\Services\Website\WebsiteSettingsService;

class PublicWebsiteContent
{
    public function __construct(
        protected WebsiteSettingsService $settings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function site(): array
    {
        $config = config('site', []);

        return array_merge($config, [
            'name' => $this->settings->get('site.name', $config['name'] ?? 'Jana Prints'),
            'tagline' => $this->settings->get('site.tagline', $config['tagline'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function seo(): array
    {
        $config = config('site.seo', []);

        return array_merge($config, [
            'title' => $this->settings->get('seo.title', $config['title'] ?? ''),
            'description' => $this->settings->get('seo.description', $config['description'] ?? ''),
            'keywords' => $this->settings->get('seo.keywords', $config['keywords'] ?? ''),
            'og_image' => $this->settings->get('seo.og_image', $config['og_image'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function footer(): array
    {
        $config = config('site.footer', []);
        $siteName = $this->site()['name'];

        return array_merge($config, [
            'tagline' => $this->settings->get('footer.tagline', $config['tagline'] ?? ''),
            'nav' => $this->settings->get('footer.nav', $config['nav'] ?? []),
            'trust_badges' => $this->settings->get('footer.trust_badges', $config['trust_badges'] ?? []),
            'social' => $this->activeSocialLinks(),
            'copyright' => $this->settings->get('footer.copyright', "© {year} {$siteName}. All rights reserved."),
            'location_suffix' => $this->settings->get('footer.location_suffix', 'Nairobi, Kenya'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function contact(): array
    {
        $config = config('conversion.contact', []);

        $latitude = $this->settings->get('contact.map_latitude', $config['map_latitude'] ?? null);
        $longitude = $this->settings->get('contact.map_longitude', $config['map_longitude'] ?? null);
        $zoom = (int) $this->settings->get('contact.map_zoom', $config['map_zoom'] ?? 15);
        $mapEnabled = $this->toBool(
            $this->settings->get('contact.map_enabled', $config['map_enabled'] ?? true),
            true,
        );

        $mapEmbed = $this->settings->get('contact.map_embed', $config['map_embed'] ?? null);

        if ($mapEnabled && empty($mapEmbed) && $latitude !== null && $longitude !== null) {
            $mapEmbed = sprintf(
                'https://maps.google.com/maps?q=%F,%F&z=%d&hl=en&ie=UTF8&iwloc=&output=embed',
                (float) $latitude,
                (float) $longitude,
                $zoom,
            );
        }

        return array_merge($config, [
            'phone' => $this->settings->get('contact.phone', $config['phone'] ?? ''),
            'phone_href' => $this->settings->get('contact.phone_href', $config['phone_href'] ?? ''),
            'email' => $this->settings->get('contact.email', $config['email'] ?? ''),
            'email_href' => $this->settings->get('contact.email_href', $config['email_href'] ?? ''),
            'address' => $this->settings->get('contact.address', $config['address'] ?? ''),
            'address_detail' => $this->settings->get('contact.address_detail', $config['address_detail'] ?? ''),
            'hours' => $this->settings->get('contact.hours', $config['hours'] ?? ''),
            'hours_weekend' => $this->settings->get('contact.hours_weekend', $config['hours_weekend'] ?? ''),
            'map_latitude' => $latitude,
            'map_longitude' => $longitude,
            'map_zoom' => $zoom,
            'map_enabled' => $mapEnabled,
            'map_embed' => $mapEnabled ? $mapEmbed : null,
            'map_url' => $this->settings->get('contact.map_url', $config['map_url'] ?? ''),
            'map_placeholder' => $this->settings->get('contact.map_placeholder', $config['map_placeholder'] ?? $config['address'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function whatsapp(): array
    {
        $config = config('conversion.whatsapp', []);

        return array_merge($config, [
            'number' => $this->settings->get('whatsapp.number', $config['number'] ?? ''),
            'message' => $this->settings->get('whatsapp.message', $config['message'] ?? ''),
            'link' => $this->settings->get('whatsapp.link', $config['link'] ?? null),
            'response_time' => $this->settings->get(
                'whatsapp.response_time',
                $config['response_time'] ?? 'Typically within 15 minutes',
            ),
        ]);
    }

    public function whatsappUrl(): string
    {
        $whatsapp = $this->whatsapp();
        $link = $whatsapp['link'] ?? null;

        if ($this->isValidUrl($link)) {
            return (string) $link;
        }

        $number = preg_replace('/\D+/', '', (string) ($whatsapp['number'] ?? ''));

        if ($number === '') {
            return '#';
        }

        $message = (string) ($whatsapp['message'] ?? '');

        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    public function footerCopyrightLine(): string
    {
        $footer = $this->footer();
        $copyright = (string) ($footer['copyright'] ?? '');

        return str_replace('{year}', (string) date('Y'), $copyright);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function activeSocialLinks(): array
    {
        $configSocial = config('site.footer.social', []);
        $jsonSocial = $this->settings->get('footer.social', $configSocial);
        $jsonSocial = is_array($jsonSocial) ? $jsonSocial : $configSocial;

        $platformKeys = [
            'facebook' => 'social.facebook',
            'instagram' => 'social.instagram',
            'linkedin' => 'social.linkedin',
            'twitter' => 'social.twitter',
        ];

        $links = [];

        foreach ($jsonSocial as $item) {
            if (! is_array($item)) {
                continue;
            }

            $icon = (string) ($item['icon'] ?? '');
            $href = (string) ($item['href'] ?? '');

            if ($icon !== '' && isset($platformKeys[$icon])) {
                $override = $this->settings->get($platformKeys[$icon]);

                if ($this->isValidUrl($override)) {
                    $href = (string) $override;
                }
            }

            if (! $this->isValidUrl($href)) {
                continue;
            }

            $links[] = [
                'label' => (string) ($item['label'] ?? ucfirst($icon)),
                'href' => $href,
                'icon' => $icon !== '' ? $icon : 'facebook',
            ];
        }

        return $links;
    }

    protected function isValidUrl(mixed $url): bool
    {
        if (! is_string($url) || $url === '' || $url === '#') {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    protected function toBool(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
