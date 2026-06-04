<?php

namespace App\Support\Communications;

use App\Models\Communications\CommunicationTemplate;
use App\Enums\CommunicationTemplateStatus;

/**
 * Entry point for future SMS, email, WhatsApp, and notification channels.
 */
class CommunicationTemplateResolver
{
    public function __construct(
        protected TemplateRenderer $renderer,
    ) {}

    public function findActive(string $code, int $companyId): ?CommunicationTemplate
    {
        return CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->where('status', CommunicationTemplateStatus::Active)
            ->first();
    }

    /**
     * @param  array<string, string|null>  $data
     * @return array{subject: ?string, body: string, template: CommunicationTemplate}|null
     */
    public function render(string $code, int $companyId, array $data): ?array
    {
        $template = $this->findActive($code, $companyId);

        if ($template === null) {
            return null;
        }

        $rendered = $this->renderer->render($template->subject, $template->body, $data);

        return [
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'template' => $template,
        ];
    }
}
