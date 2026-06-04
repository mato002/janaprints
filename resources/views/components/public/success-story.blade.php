@props(['story'])

<article class="public-success-story" data-animate="fade-up">
    <div class="public-success-story__header">
        <span class="public-success-story__type">{{ $story['client_type'] }}</span>
        <h3 class="public-success-story__title">{{ $story['title'] }}</h3>
    </div>

    <dl class="public-success-story__details">
        <div class="public-success-story__row">
            <dt>Challenge</dt>
            <dd>{{ $story['challenge'] }}</dd>
        </div>
        <div class="public-success-story__row">
            <dt>Solution</dt>
            <dd>{{ $story['solution'] }}</dd>
        </div>
        <div class="public-success-story__row public-success-story__row--outcome">
            <dt>Outcome</dt>
            <dd>{{ $story['outcome'] }}</dd>
        </div>
    </dl>
</article>
