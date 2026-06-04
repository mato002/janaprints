@props(['item', 'index' => 0, 'open' => false])

<div class="public-conversion-faq__item" data-faq-item>
    <h3>
        <button
            type="button"
            class="public-conversion-faq__trigger"
            data-faq-trigger
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            id="faq-trigger-{{ $index }}"
            aria-controls="faq-panel-{{ $index }}"
        >
            <span>{{ $item['question'] }}</span>
            <span class="public-conversion-faq__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </span>
        </button>
    </h3>
    <div
        class="public-conversion-faq__panel"
        data-faq-panel
        id="faq-panel-{{ $index }}"
        role="region"
        aria-labelledby="faq-trigger-{{ $index }}"
        @if (! $open) hidden @endif
    >
        <p>{{ $item['answer'] }}</p>
    </div>
</div>
