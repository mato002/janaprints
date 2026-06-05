<?php

namespace App\Support\Integrations;

class IntegrationSecretMask
{
    /**
     * @var list<string>
     */
    public const SENSITIVE_FIELDS = [
        'password', 'smtp_password', 'api_key', 'mailgun_api_key', 'sendgrid_api_key',
        'ses_secret_key', 'ses_access_key', 'secret', 'secret_hash', 'config',
    ];

    public static function mask(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', 8).'…'.substr($value, -4);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function maskArray(array $values): array
    {
        $masked = [];

        foreach ($values as $key => $value) {
            if (in_array($key, self::SENSITIVE_FIELDS, true)) {
                $masked[$key] = is_string($value) ? self::mask($value) : '••••••••';
            } elseif (is_array($value)) {
                $masked[$key] = self::maskArray($value);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }
}
