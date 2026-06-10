@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'compact-workspace-header']) }}>
    <div class="compact-workspace-header__row">
        <div class="compact-workspace-header__title-group min-w-0">
            <h1 class="compact-workspace-header__title truncate">{{ $title }}</h1>
            @if ($description)
                <p class="compact-workspace-header__description truncate">{{ $description }}</p>
            @endif
        </div>

        @isset($search)
            <div class="compact-workspace-header__search relative shrink-0">
                {{ $search }}
            </div>
        @endisset

        @isset($actions)
            <div class="compact-workspace-header__actions shrink-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
