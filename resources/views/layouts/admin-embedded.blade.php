<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        @isset($header)
            <div class="mb-4">{{ $header }}</div>
        @endisset
        {{ $slot }}
    </div>
</turbo-frame>
