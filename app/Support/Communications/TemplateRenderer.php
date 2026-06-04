<?php

namespace App\Support\Communications;

class TemplateRenderer
{
    public function __construct(
        protected TemplateVariableEngine $variables,
    ) {}

    /**
     * @param  array<string, string|null>  $data
     * @return array{subject: ?string, body: string, variables: list<string>, validation: array{missing: list<string>, unknown: list<string>, valid: list<string>}}
     */
    public function render(?string $subject, string $body, array $data): array
    {
        $validation = $this->variables->validate($subject ?? '', $body, $data);

        return [
            'subject' => $subject !== null ? $this->interpolate($subject, $data) : null,
            'body' => $this->interpolate($body, $data),
            'variables' => $this->variables->extract(($subject ?? '').' '.$body),
            'validation' => $validation,
        ];
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function interpolate(string $content, array $data): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-z][a-z0-9_]*)\s*\}\}/i',
            function (array $matches) use ($data) {
                $key = strtolower($matches[1]);

                return (string) ($data[$key] ?? $matches[0]);
            },
            $content,
        );
    }
}
