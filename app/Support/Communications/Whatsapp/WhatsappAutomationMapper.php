<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\CommunicationChannel;
use App\Enums\WhatsappAutomationEvent;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\WhatsappTemplate;

/**
 * Maps ERP domain events to WhatsApp template bindings (no automatic sending).
 */
class WhatsappAutomationMapper
{
    /**
     * @return array<string, array{event: WhatsappAutomationEvent, category: string, template: ?CommunicationTemplate, binding: ?WhatsappTemplate}>
     */
    public function mapForCompany(int $companyId): array
    {
        $bindings = WhatsappTemplate::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->get()
            ->keyBy(fn (WhatsappTemplate $t) => $t->automation_event?->value);

        $map = [];
        foreach (WhatsappAutomationEvent::mappable() as $event) {
            $binding = $bindings->get($event->value);
            $category = $event->templateCategory();
            $template = CommunicationTemplate::query()
                ->where('company_id', $companyId)
                ->where('channel', CommunicationChannel::WhatsApp)
                ->where('category', $category)
                ->where('status', 'active')
                ->first();

            $map[$event->value] = [
                'event' => $event,
                'category' => $category->value,
                'category_label' => $category->label(),
                'template' => $template,
                'binding' => $binding,
            ];
        }

        return $map;
    }

    public function resolveTemplate(int $companyId, WhatsappAutomationEvent $event): ?WhatsappTemplate
    {
        return WhatsappTemplate::query()
            ->where('company_id', $companyId)
            ->where('automation_event', $event)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->first();
    }
}
