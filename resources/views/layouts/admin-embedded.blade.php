<turbo-frame id="module-workspace-content" class="flex min-h-0 flex-1 flex-col overflow-hidden">
    <div class="module-workspace-embedded flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-x-hidden overflow-y-auto overscroll-y-contain">
        @include('admin.partials.alerts')
        @isset($header)
            <div class="mb-2">{{ $header }}</div>
        @endisset
        {{ $slot }}
    </div>
</turbo-frame>
