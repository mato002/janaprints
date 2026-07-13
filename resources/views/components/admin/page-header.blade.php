@props(['title', 'description' => null])

{{--
  Canonical page chrome action order (right side):
  secondary → export → primary (Create / New)
  so Export is always in the same place relative to the primary CTA.
--}}
<div {{ $attributes->merge(['class' => 'workspace-page-header mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="text-dashboard-title text-erp-primary truncate">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @if (isset($secondary) || isset($export) || isset($actions) || ! $slot->isEmpty())
        <div class="workspace-page-header__actions flex shrink-0 flex-wrap items-center justify-end gap-2">
            @isset($secondary)
                {{ $secondary }}
            @endisset
            @isset($export)
                {{ $export }}
            @endisset
            @isset($actions)
                {{ $actions }}
            @elseif (! $slot->isEmpty())
                {{ $slot }}
            @endif
        </div>
    @endif
</div>
