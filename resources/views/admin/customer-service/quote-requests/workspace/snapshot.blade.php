@php
    $snap = $workspace['snapshot'];
@endphp

<section class="qr-360__card">
    <h2 class="qr-360__card-title">{{ __('Customer & Request Snapshot') }}</h2>

    <div class="qr-360__snapshot-grid">
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Customer Name') }}</span>
            <span class="qr-360__field-value">{{ $snap['name'] }}</span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Company') }}</span>
            <span class="qr-360__field-value">{{ $snap['company'] ?: '—' }}</span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Phone') }}</span>
            <a href="tel:{{ preg_replace('/\s+/', '', $snap['phone']) }}" class="qr-360__field-link">{{ $snap['phone'] }}</a>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Email') }}</span>
            <a href="mailto:{{ $snap['email'] }}" class="qr-360__field-link">{{ $snap['email'] }}</a>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Service Requested') }}</span>
            <span class="qr-360__field-value">{{ $snap['service'] }}</span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Quantity') }}</span>
            <span class="qr-360__field-value">{{ $snap['quantity'] }}</span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Deadline') }}</span>
            <span class="qr-360__field-value">{{ $snap['deadline'] }}</span>
        </div>
        <div class="qr-360__field">
            <span class="qr-360__field-label">{{ __('Source') }}</span>
            <span class="qr-360__field-value">{{ $snap['source'] }}</span>
        </div>
    </div>

    <div class="qr-360__note-card">
        <p class="qr-360__note-card-label">{{ __('Customer Notes') }}</p>
        <p class="qr-360__note-card-body whitespace-pre-wrap">{{ $snap['message'] }}</p>
    </div>
</section>
