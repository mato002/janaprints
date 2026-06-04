@props(['member'])

<article class="public-facility-team" data-animate="fade-up">
    <div class="public-facility-team__photo">
        <x-public.media-image
            :src="$member['photo']"
            :alt="$member['alt']"
            fallback="portrait"
            class="h-full w-full object-cover"
        />
    </div>
    <div class="public-facility-team__body">
        <h4 class="public-facility-team__name">{{ $member['name'] }}</h4>
        <p class="public-facility-team__role">{{ $member['role'] }}</p>
        <p class="public-facility-team__bio">{{ $member['bio'] }}</p>
    </div>
</article>
