<div class="comm-log-360__grid comm-log-360__grid--overview">
    <section class="comm-log-360__card comm-log-360__card--message">
        <h2 class="comm-log-360__card-title">{{ __('Message') }}</h2>
        @if ($log->subject && $bubbleTone === 'email')
            <p class="comm-log-360__email-subject">{{ $log->subject }}</p>
        @endif
        <div class="comm-log-360__bubble-wrap">
            <div class="comm-log-360__bubble comm-log-360__bubble--{{ $bubbleTone }}">
                @if ($log->message_body)
                    <div class="comm-log-360__bubble-body">{!! $messageBodyHtml !!}</div>
                @else
                    <p class="comm-log-360__bubble-empty">{{ __('No message body recorded') }}</p>
                @endif
                <p class="comm-log-360__bubble-meta">{{ $log->created_at?->format('d M Y • H:i') }}</p>
            </div>
        </div>
        @if ($log->attachments->isNotEmpty())
            <div class="comm-log-360__attachments">
                <p class="comm-log-360__attachments-label">{{ __('Attachments') }}</p>
                <ul class="comm-log-360__attachments-list" role="list">
                    @foreach ($log->attachments as $attachment)
                        <li class="comm-log-360__attachment-item">
                            <span class="comm-log-360__attachment-icon" aria-hidden="true">📎</span>
                            <span>{{ $attachment->attachment_type->label() }} — {{ $attachment->label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <section class="comm-log-360__card">
        <h2 class="comm-log-360__card-title">{{ __('Communication summary') }}</h2>
        <dl class="comm-log-360__dl">
            <div>
                <dt>{{ __('Channel') }}</dt>
                <dd>{{ $log->channel->label() }}</dd>
            </div>
            <div>
                <dt>{{ __('Status') }}</dt>
                <dd><span class="comm-log-360__badge {{ $log->status->badgeClass() }}">{{ $log->status->label() }}</span></dd>
            </div>
            <div>
                <dt>{{ __('Type') }}</dt>
                <dd>{{ $log->communication_type->label() }}</dd>
            </div>
            <div>
                <dt>{{ __('Template') }}</dt>
                <dd>{{ $log->template_code ?? $log->template?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Created by') }}</dt>
                <dd>{{ $log->creator?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Sent by') }}</dt>
                <dd>{{ $log->sentByUser?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Created at') }}</dt>
                <dd>{{ $log->created_at?->format('d M Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt>{{ __('Sent at') }}</dt>
                <dd>{{ $log->sent_at?->format('d M Y H:i') ?? '—' }}</dd>
            </div>
            @if ($log->branch)
                <div>
                    <dt>{{ __('Branch') }}</dt>
                    <dd>{{ $log->branch->name }}</dd>
                </div>
            @endif
            @if ($log->priority)
                <div>
                    <dt>{{ __('Priority') }}</dt>
                    <dd><span class="comm-log-360__badge {{ $log->priority->badgeClass() }}">{{ $log->priority->label() }}</span></dd>
                </div>
            @endif
        </dl>
    </section>
</div>
