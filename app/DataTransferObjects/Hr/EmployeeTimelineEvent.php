<?php

namespace App\DataTransferObjects\Hr;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

readonly class EmployeeTimelineEvent
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
        public string $category,
        public string $icon = 'clock',
        public array $metadata = [],
    ) {}

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
            'event_date' => $this->eventDatetime->format('M j, Y'),
            'actor_name' => $this->actorName,
            'category' => $this->category,
            'icon' => $this->icon,
            'metadata' => $this->metadata,
        ];
    }

    public static function make(
        string $eventType,
        string $title,
        CarbonInterface|string $when,
        string $category,
        ?string $description = null,
        ?string $actorName = null,
        string $icon = 'clock',
    ): self {
        return new self(
            eventType: $eventType,
            title: $title,
            description: $description,
            eventDatetime: $when instanceof CarbonInterface ? $when : Carbon::parse($when),
            actorName: $actorName,
            category: $category,
            icon: $icon,
        );
    }
}
