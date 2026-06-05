@php
    $whatsappUrl = 'https://wa.me/' . $whatsapp['number'] . '?text=' . rawurlencode($whatsapp['message']);
    $local = config('site.local');
@endphp

<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Contact Jana Prints"
        intro="Reach our Nairobi team for printing enquiries, quotes, artwork support and nationwide delivery questions."
        badge="Contact"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-8 lg:grid-cols-2">
                <div class="public-card public-card--soft p-8" data-animate="fade-up">
                    <h2 class="font-display text-2xl font-bold">Get in touch</h2>
                    <dl class="mt-6 space-y-4 text-sm">
                        <div>
                            <dt class="font-semibold">Phone</dt>
                            <dd><a href="{{ $contact['phone_href'] }}">{{ $local['phone'] ?: $contact['phone'] }}</a></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Email</dt>
                            <dd><a href="{{ $contact['email_href'] }}">{{ $local['email'] ?: $contact['email'] }}</a></dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Address</dt>
                            <dd>{{ $local['address'] ?: $contact['address'] }}, {{ $local['city'] }}, {{ $local['country'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">Hours</dt>
                            <dd>{{ $contact['hours'] }}<br>{{ $contact['hours_weekend'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">WhatsApp</dt>
                            <dd><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a></dd>
                        </div>
                    </dl>
                </div>

                <div class="public-card public-card--soft p-8" data-animate="fade-up">
                    <h2 class="font-display text-2xl font-bold">Service areas</h2>
                    <p class="mt-4 text-sm leading-relaxed text-brand-text-secondary">
                        We serve clients across Kenya with collection from Nairobi and nationwide delivery for campaigns, institutions and recurring print needs.
                    </p>
                    <ul class="mt-6 flex flex-wrap gap-2">
                        @foreach ($local['service_areas'] as $area)
                            <li class="public-footer__badge">{{ $area }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-8">
                        <x-public.button href="{{ route('storefront.quote') }}" variant="gradient" size="lg">Request a Quote</x-public.button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
