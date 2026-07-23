@php
    $snap = $workspace['snapshot'];
@endphp

<x-admin.record-workspace.section :title="__('Request details')" tone="muted">
    <div class="rw-hero-snapshot">
        <div class="rw-hero-snapshot__grid">
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Phone') }}</span>
                <a href="tel:{{ preg_replace('/\s+/', '', $snap['phone']) }}" class="rw-hero-snapshot__link">{{ $snap['phone'] }}</a>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Email') }}</span>
                <a href="mailto:{{ $snap['email'] }}" class="rw-hero-snapshot__link">{{ $snap['email'] }}</a>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Service') }}</span>
                <span class="rw-hero-snapshot__field-value">{{ $snap['service'] }}</span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Quantity') }}</span>
                <span class="rw-hero-snapshot__field-value">{{ $snap['quantity'] }}</span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Deadline') }}</span>
                <span class="rw-hero-snapshot__field-value">{{ $snap['deadline'] }}</span>
            </div>
            <div>
                <span class="rw-hero-snapshot__field-label">{{ __('Source') }}</span>
                <span class="rw-hero-snapshot__field-value">{{ $snap['source'] }}</span>
            </div>
        </div>

        @if ($snap['message'])
            <div class="rw-hero-snapshot__note">
                <p class="rw-hero-snapshot__note-label">{{ __('Customer notes') }}</p>
                <p class="whitespace-pre-wrap">{{ $snap['message'] }}</p>
            </div>
        @endif
    </div>
</x-admin.record-workspace.section>
