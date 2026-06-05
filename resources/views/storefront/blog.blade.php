<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Print Guides & Resources"
        :intro="$blog['placeholder_message']"
        badge="Guides"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container text-center" data-animate="fade-up">
            <p class="mx-auto max-w-2xl text-base leading-relaxed text-brand-text-secondary">
                This section will host practical printing guides for artwork preparation, product selection, turnaround planning and bulk order tips.
            </p>
            <div class="mt-8">
                <x-public.button href="{{ route('storefront.contact') }}" variant="gradient" size="lg">Talk to Our Team</x-public.button>
            </div>
        </div>
    </section>
</x-layouts.public>
