@props([
    'steps' => [],
    'label' => null,
])

<nav class="rw-workflow" aria-label="{{ $label ?? __('Workflow progress') }}">
    <ol class="rw-workflow__track" role="list">
        @foreach ($steps as $step)
            @php
                $state = $step['state'] ?? 'future'; // done | current | future
            @endphp
            <li @class(['rw-workflow__step', 'rw-workflow__step--'.$state])>
                <span class="rw-workflow__marker" aria-hidden="true">
                    @if ($state === 'done')
                        ✓
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <span class="rw-workflow__label">{{ $step['label'] }}</span>
                @if (! empty($step['url']) && $state !== 'future')
                    <a
                        href="{{ $step['url'] }}"
                        class="rw-workflow__link"
                        data-turbo-frame="erp-main"
                        @if ($state === 'current') aria-current="step" @endif
                    >
                        <span class="sr-only">{{ $step['label'] }}</span>
                    </a>
                @elseif ($state === 'current')
                    <span class="sr-only">{{ __('Current step') }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
