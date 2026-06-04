<?php

namespace App\DataTransferObjects\Crm;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

readonly class CustomerTimelineEvent
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $eventType,
        public string $title,
        public ?string $description,
        public CarbonInterface $eventDatetime,
        public ?string $actorName,
        public string $actorType,
        public string $sourceType,
        public int|string $sourceId,
        public ?string $sourceUrl,
        public string $icon,
        public string $color,
        public array $metadata = [],
        public string $category = 'all',
    ) {}

    public static function fromRow(object $row): self
    {
        $metadata = json_decode($row->metadata ?? '{}', true);
        if (! is_array($metadata)) {
            $metadata = [];
        }

        $sourceUrl = null;
        if (! empty($row->source_route) && Route::has($row->source_route)) {
            $param = $row->source_route_param ?? 'id';
            $sourceUrl = route($row->source_route, [$param => $row->source_id]);
            if (! empty($metadata['tab'])) {
                $sourceUrl .= (str_contains($sourceUrl, '?') ? '&' : '?').'tab='.$metadata['tab'];
            }
        }

        return new self(
            eventType: (string) $row->event_type,
            title: (string) $row->title,
            description: $row->description ? (string) $row->description : null,
            eventDatetime: Carbon::parse($row->event_datetime),
            actorName: $row->actor_name ? (string) $row->actor_name : null,
            actorType: (string) ($row->actor_type ?? 'system'),
            sourceType: (string) $row->source_type,
            sourceId: $row->source_id,
            sourceUrl: $sourceUrl,
            icon: (string) ($row->icon ?? 'clock'),
            color: (string) ($row->color ?? 'slate'),
            metadata: $metadata,
            category: (string) ($row->category ?? 'all'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'description' => $this->description,
            'event_datetime' => $this->eventDatetime->toIso8601String(),
            'event_date' => $this->eventDatetime->format('Y-m-d'),
            'event_time' => $this->eventDatetime->format('H:i'),
            'actor_name' => $this->actorName,
            'actor_type' => $this->actorType,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'source_url' => $this->sourceUrl,
            'icon' => $this->icon,
            'color' => $this->color,
            'metadata' => $this->metadata,
            'category' => $this->category,
        ];
    }
}
