<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\WhatsappAutomationEvent;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\WhatsappTemplate;
use Illuminate\Support\Collection;

class WhatsappTemplateService
{
    public function __construct(
        protected WhatsappAutomationMapper $automation,
    ) {}

    /**
     * @return Collection<int, WhatsappTemplate>
     */
    public function listBindings(int $companyId): Collection
    {
        return WhatsappTemplate::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['communicationTemplate', 'account'])
            ->orderBy('automation_event')
            ->get();
    }

    public function syncFromCom1(int $companyId, int $userId): int
    {
        $templates = CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('channel', CommunicationChannel::WhatsApp)
            ->where('status', CommunicationTemplateStatus::Active)
            ->get();

        $synced = 0;
        foreach ($templates as $template) {
            $automationEvent = $this->guessAutomationEvent($template);
            WhatsappTemplate::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'communication_template_id' => $template->id,
                ],
                [
                    'automation_event' => $automationEvent,
                    'is_active' => true,
                ],
            );
            $synced++;
        }

        return $synced;
    }

    public function bind(
        int $companyId,
        int $communicationTemplateId,
        ?WhatsappAutomationEvent $automationEvent = null,
        ?int $whatsappAccountId = null,
    ): WhatsappTemplate {
        return WhatsappTemplate::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'communication_template_id' => $communicationTemplateId,
            ],
            [
                'whatsapp_account_id' => $whatsappAccountId,
                'automation_event' => $automationEvent,
                'is_active' => true,
            ],
        );
    }

    protected function guessAutomationEvent(CommunicationTemplate $template): ?WhatsappAutomationEvent
    {
        foreach (WhatsappAutomationEvent::mappable() as $event) {
            if ($event->templateCategory() === $template->category) {
                return $event;
            }
        }

        return null;
    }
}
