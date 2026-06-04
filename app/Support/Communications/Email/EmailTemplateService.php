<?php

namespace App\Support\Communications\Email;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\EmailAutomationEvent;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailTemplate;
use Illuminate\Support\Collection;

class EmailTemplateService
{
    /**
     * @return Collection<int, EmailTemplate>
     */
    public function listBindings(int $companyId): Collection
    {
        return EmailTemplate::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['communicationTemplate', 'account'])
            ->orderBy('automation_event')
            ->get();
    }

    public function syncFromCom1(int $companyId): int
    {
        $templates = CommunicationTemplate::query()
            ->where('company_id', $companyId)
            ->where('channel', CommunicationChannel::Email)
            ->where('status', CommunicationTemplateStatus::Active)
            ->get();

        $synced = 0;
        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['company_id' => $companyId, 'communication_template_id' => $template->id],
                ['automation_event' => $this->guessAutomationEvent($template), 'is_active' => true],
            );
            $synced++;
        }

        return $synced;
    }

    protected function guessAutomationEvent(CommunicationTemplate $template): ?EmailAutomationEvent
    {
        foreach (EmailAutomationEvent::mappable() as $event) {
            if ($event->templateCategory() === $template->category) {
                return $event;
            }
        }

        return null;
    }
}
