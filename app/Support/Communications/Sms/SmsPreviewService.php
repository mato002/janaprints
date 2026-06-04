<?php

namespace App\Support\Communications\Sms;

use App\Models\Communications\CommunicationTemplate;
use App\Support\Communications\TemplateRenderer;
use App\Support\Communications\TemplateVariableEngine;

class SmsPreviewService
{
    public function __construct(
        protected TemplateRenderer $renderer,
        protected TemplateVariableEngine $variables,
        protected SmsSegmentCalculator $segments,
    ) {}

    /**
     * @param  array<string, string|null>  $sampleData
     * @return array<string, mixed>
     */
    public function preview(?CommunicationTemplate $template, string $messageTemplate, array $sampleData = []): array
    {
        $data = array_merge($this->variables->sampleData(), $sampleData);
        $body = $template?->body ?? $messageTemplate;

        $rendered = $this->renderer->render(null, $body, $data);
        $segmentInfo = $this->segments->calculate($rendered['body']);

        return [
            'body' => $rendered['body'],
            'variables' => $rendered['variables'],
            'validation' => $rendered['validation'],
            'character_count' => $segmentInfo['characters'],
            'segments' => $segmentInfo['segments'],
            'sample_data' => $data,
        ];
    }
}
