<?php

namespace App\Support\Communications\Email;

use App\Enums\CommunicationChannel;
use App\Enums\EmailAutomationEvent;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailTemplate;

class EmailAutomationMapper
{
    /**
     * @return array<string, array{event: EmailAutomationEvent, category_label: string, template: ?CommunicationTemplate, binding: ?EmailTemplate}>
     */
    public function mapForCompany(int $companyId): array
    {
        $bindings = EmailTemplate::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->get()
            ->keyBy(fn (EmailTemplate $t) => $t->automation_event?->value);

        $map = [];
        foreach (EmailAutomationEvent::mappable() as $event) {
            $category = $event->templateCategory();
            $map[$event->value] = [
                'event' => $event,
                'category_label' => $category->label(),
                'template' => CommunicationTemplate::query()
                    ->where('company_id', $companyId)
                    ->where('channel', CommunicationChannel::Email)
                    ->where('category', $category)
                    ->where('status', 'active')
                    ->first(),
                'binding' => $bindings->get($event->value),
            ];
        }

        return $map;
    }
}
