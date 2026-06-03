@props(['title', 'description' => null])

<section {{ $attributes->merge(['class' => 'space-y-5']) }}>
    <div>
        <h3 class="text-sm font-semibold text-erp-primary">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    <div class="erp-form-grid">
        {{ $slot }}
    </div>
</section>
