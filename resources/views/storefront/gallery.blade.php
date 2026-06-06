<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Our Work Gallery"
        intro="Browse print, branding, packaging and large-format projects delivered for businesses, schools, NGOs, events and corporates across Kenya."
        badge="Gallery"
        :breadcrumbs="$breadcrumbs"
        :wide="true"
    />

    <x-public.portfolio-section
        :full-page="true"
        :show-gallery-cta="false"
        heading="Print & Branding Projects"
        intro="Browse print, branding, packaging and large-format work by category."
    />
</x-layouts.public>
