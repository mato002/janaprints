@php
    $categoryTone = [
        'lifecycle' => 'indigo',
        'compensation' => 'violet',
        'payroll' => 'violet',
        'leave' => 'sky',
        'attendance' => 'info',
        'training' => 'teal',
        'performance' => 'amber',
        'documents' => 'slate',
        'warning' => 'warning',
        'exit' => 'danger',
        'profile' => 'slate',
    ];
@endphp

<section class="employee-360__timeline-card">
    <div class="employee-360__card-head">
        <div class="employee-360__card-title-wrap">
            <span class="employee-360__card-icon employee-360__card-icon--timeline" aria-hidden="true">
                <x-admin.icon name="clock" class="h-4 w-4" />
            </span>
            <h2 class="employee-360__card-title">{{ __('Employee Timeline') }}</h2>
        </div>
        <span class="employee-360__timeline-count">{{ $timeline->count() }} {{ __('events') }}</span>
    </div>

    @if ($timeline->isEmpty())
        <div class="employee-360__empty-block employee-360__empty-block--lg">
            <p>{{ __('No timeline events yet.') }}</p>
            <p class="employee-360__empty-hint">{{ __('Clock-ins, leave, payroll, training, and profile changes will appear here.') }}</p>
        </div>
    @else
        <ol class="employee-360__timeline">
            @foreach ($timeline as $event)
                @php
                    $tone = $categoryTone[strtolower($event->category)] ?? 'slate';
                @endphp
                <li class="employee-360__timeline-item employee-360__timeline-item--{{ $tone }}">
                    <span class="employee-360__timeline-dot" aria-hidden="true"></span>
                    <div class="employee-360__timeline-body">
                        <div class="employee-360__timeline-top">
                            <time class="employee-360__timeline-time" datetime="{{ $event->eventDatetime->toIso8601String() }}">
                                {{ $event->eventDatetime->format('d M Y · H:i') }}
                            </time>
                            <span class="employee-360__timeline-cat">{{ $event->category }}</span>
                        </div>
                        <p class="employee-360__timeline-title">{{ $event->title }}</p>
                        @if ($event->description)
                            <p class="employee-360__timeline-desc">{{ $event->description }}</p>
                        @endif
                        @if ($event->actorName)
                            <p class="employee-360__timeline-actor">{{ __('By') }} {{ $event->actorName }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
