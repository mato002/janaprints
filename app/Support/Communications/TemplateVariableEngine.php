<?php

namespace App\Support\Communications;

class TemplateVariableEngine
{
    /**
     * @return list<string>
     */
    public function extract(string $content): array
    {
        preg_match_all('/\{\{\s*([a-z][a-z0-9_]*)\s*\}\}/i', $content, $matches);

        $keys = array_map('strtolower', $matches[1] ?? []);

        return array_values(array_unique($keys));
    }

    /**
     * @param  array<string, string|null>  $data
     * @return array{missing: list<string>, unknown: list<string>, valid: list<string>}
     */
    public function validate(string $subject, string $body, array $data = []): array
    {
        $required = $this->extract($subject.' '.$body);
        $registry = array_keys(config('communication_variables', []));
        $provided = array_map('strtolower', array_keys($data));

        $missing = [];
        $unknown = [];
        $valid = [];

        foreach ($required as $key) {
            if (! in_array($key, $registry, true)) {
                $unknown[] = $key;

                continue;
            }

            if ($data !== [] && (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '')) {
                $missing[] = $key;
            } else {
                $valid[] = $key;
            }
        }

        return [
            'missing' => $missing,
            'unknown' => $unknown,
            'valid' => $valid,
        ];
    }

    /**
     * @return list<array{key: string, label: string, description: string, sample: string}>
     */
    public function definitions(): array
    {
        $definitions = [];

        foreach (config('communication_variables', []) as $key => $meta) {
            $definitions[] = [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'description' => $meta['description'] ?? '',
                'sample' => $meta['sample'] ?? '',
            ];
        }

        return $definitions;
    }

    /**
     * @return array<string, string>
     */
    public function sampleData(): array
    {
        $samples = [];

        foreach (config('communication_variables', []) as $key => $meta) {
            $samples[$key] = (string) ($meta['sample'] ?? '');
        }

        return $samples;
    }
}
