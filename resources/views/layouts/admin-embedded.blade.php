<turbo-frame id="module-workspace-content">
    <div class="module-workspace-embedded w-full min-w-0">
        @include('admin.partials.alerts')
        @isset($header)
            <div class="mb-2">{{ $header }}</div>
        @endisset
        {{ $slot }}
    </div>
</turbo-frame>
