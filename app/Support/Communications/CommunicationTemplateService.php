<?php

namespace App\Support\Communications;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationTemplateCategory;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\CommunicationTemplateType;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\CommunicationTemplateVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommunicationTemplateService
{
    public function __construct(
        protected TemplateVariableEngine $variables,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor, int $companyId): CommunicationTemplate
    {
        return DB::transaction(function () use ($data, $actor, $companyId) {
            $template = CommunicationTemplate::query()->create([
                'company_id' => $companyId,
                'code' => $data['code'] ?? $this->nextCode($companyId, (string) $data['channel'], (string) $data['category']),
                'name' => $data['name'],
                'category' => $data['category'],
                'channel' => $data['channel'],
                'template_type' => $data['template_type'],
                'subject' => $data['subject'] ?? null,
                'body' => $data['body'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? CommunicationTemplateStatus::Active->value,
                'version_number' => 1,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->recordVersion($template, $actor, $data['change_notes'] ?? __('Initial version'), null);

            return $template->fresh(['creator', 'updater']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CommunicationTemplate $template, array $data, User $actor): CommunicationTemplate
    {
        $this->assertKnownVariables($data['subject'] ?? $template->subject, $data['body'] ?? $template->body);

        return DB::transaction(function () use ($template, $data, $actor) {
            $previousVersion = $template->versions()->orderByDesc('version_number')->first();
            $nextVersion = $template->version_number + 1;

            $template->fill([
                'name' => $data['name'] ?? $template->name,
                'category' => $data['category'] ?? $template->category,
                'channel' => $data['channel'] ?? $template->channel,
                'template_type' => $data['template_type'] ?? $template->template_type,
                'subject' => array_key_exists('subject', $data) ? $data['subject'] : $template->subject,
                'body' => $data['body'] ?? $template->body,
                'description' => array_key_exists('description', $data) ? $data['description'] : $template->description,
                'status' => $data['status'] ?? $template->status,
                'version_number' => $nextVersion,
                'updated_by' => $actor->id,
            ]);
            $template->save();

            $this->recordVersion(
                $template,
                $actor,
                $data['change_notes'] ?? __('Content updated'),
                $previousVersion,
            );

            return $template->fresh(['creator', 'updater']);
        });
    }

    public function restore(CommunicationTemplate $template, CommunicationTemplateVersion $version, User $actor, ?string $changeNotes = null): CommunicationTemplate
    {
        if ($version->communication_template_id !== $template->id) {
            throw ValidationException::withMessages([
                'version' => __('Version does not belong to this template.'),
            ]);
        }

        return $this->update($template, [
            'subject' => $version->subject,
            'body' => $version->body,
            'change_notes' => $changeNotes ?? __('Restored from version :version', ['version' => $version->version_number]),
        ], $actor);
    }

    /**
     * @return array{left: array<string, mixed>, right: array<string, mixed>, diff: array<string, array{changed: bool, left: ?string, right: ?string}>}
     */
    public function compare(CommunicationTemplateVersion $left, CommunicationTemplateVersion $right): array
    {
        $fields = ['subject', 'body', 'change_notes'];

        $diff = [];

        foreach ($fields as $field) {
            $leftValue = $left->{$field};
            $rightValue = $right->{$field};
            $diff[$field] = [
                'changed' => $leftValue !== $rightValue,
                'left' => $leftValue,
                'right' => $rightValue,
            ];
        }

        return [
            'left' => $this->versionPayload($left),
            'right' => $this->versionPayload($right),
            'diff' => $diff,
        ];
    }

    protected function recordVersion(
        CommunicationTemplate $template,
        User $actor,
        string $changeNotes,
        ?CommunicationTemplateVersion $previousVersion,
    ): CommunicationTemplateVersion {
        return CommunicationTemplateVersion::query()->create([
            'communication_template_id' => $template->id,
            'version_number' => $template->version_number,
            'previous_version_id' => $previousVersion?->id,
            'subject' => $template->subject,
            'body' => $template->body,
            'change_notes' => $changeNotes,
            'created_by' => $actor->id,
        ]);
    }

    protected function assertKnownVariables(?string $subject, string $body): void
    {
        $validation = $this->variables->validate($subject ?? '', $body);

        if ($validation['unknown'] !== []) {
            throw ValidationException::withMessages([
                'body' => __('Unknown placeholders: :keys', ['keys' => implode(', ', $validation['unknown'])]),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function versionPayload(CommunicationTemplateVersion $version): array
    {
        return [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'subject' => $version->subject,
            'body' => $version->body,
            'change_notes' => $version->change_notes,
            'created_by' => $version->creator?->name,
            'created_at' => $version->created_at?->toIso8601String(),
            'previous_version_id' => $version->previous_version_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templatePayload(CommunicationTemplate $template): array
    {
        $variables = $this->variables->extract(($template->subject ?? '').' '.$template->body);

        return [
            'id' => $template->id,
            'code' => $template->code,
            'name' => $template->name,
            'category' => $template->category->value,
            'category_label' => $template->category->label(),
            'category_group' => $template->category->group(),
            'channel' => $template->channel->value,
            'channel_label' => $template->channel->label(),
            'template_type' => $template->template_type->value,
            'template_type_label' => $template->template_type->label(),
            'subject' => $template->subject,
            'body' => $template->body,
            'description' => $template->description,
            'status' => $template->status->value,
            'status_label' => $template->status->label(),
            'version_number' => $template->version_number,
            'variables' => $variables,
            'created_by' => $template->creator?->name,
            'updated_by' => $template->updater?->name,
            'updated_at' => $template->updated_at?->toIso8601String(),
        ];
    }

    public function requiresSubject(CommunicationChannel $channel): bool
    {
        return in_array($channel, [CommunicationChannel::Email, CommunicationChannel::Notification], true);
    }

    public function categoryOptions(): array
    {
        return array_map(
            fn (CommunicationTemplateCategory $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'group' => $case->group(),
            ],
            CommunicationTemplateCategory::cases(),
        );
    }

    public function channelOptions(): array
    {
        return array_map(
            fn (CommunicationChannel $case) => ['value' => $case->value, 'label' => $case->label()],
            CommunicationChannel::cases(),
        );
    }

    public function typeOptions(): array
    {
        return array_map(
            fn (CommunicationTemplateType $case) => ['value' => $case->value, 'label' => $case->label()],
            CommunicationTemplateType::cases(),
        );
    }

    public function statusOptions(): array
    {
        return array_map(
            fn (CommunicationTemplateStatus $case) => ['value' => $case->value, 'label' => $case->label()],
            CommunicationTemplateStatus::cases(),
        );
    }

    protected function nextCode(int $companyId, string $channel, string $category): string
    {
        $base = Str::lower($channel).'-'.Str::slug($category, '_');
        $code = $base;
        $suffix = 1;

        while (
            CommunicationTemplate::query()
                ->where('company_id', $companyId)
                ->where('code', $code)
                ->exists()
        ) {
            $suffix++;
            $code = $base.'-'.$suffix;
        }

        return $code;
    }
}
