@props(['project'])

<article class="public-recent-work-card">
    <div class="public-recent-work-card__image">
        <x-public.media-image
            :src="$project['image']"
            :alt="$project['alt']"
            fallback="print_press"
            class="h-full w-full object-cover"
            width="640"
            height="400"
        />
    </div>
    <div class="public-recent-work-card__body">
        <h3 class="public-recent-work-card__title">{{ $project['title'] }}</h3>
        <ul class="public-recent-work-card__list">
            @foreach ($project['highlights'] as $highlight)
                <li>{{ $highlight }}</li>
            @endforeach
        </ul>
    </div>
</article>
