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
                    <h2 class="font-display text-2xl font-bold">Send us a message</h2>
                    <p class="mt-2 text-sm text-brand-text-secondary">We typically respond within one business day.</p>

                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        class="public-conversion-form public-conversion-form--light mt-6"
                        data-contact-form
                        method="POST"
                        action="{{ route('public.contact-messages.store') }}"
                        novalidate
                    >
                        @csrf
                        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">
                        <input type="text" name="_gotcha" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">

                        <div class="public-conversion-form__grid">
                            <div class="public-conversion-form__field">
                                <label for="contact-name">Name <span aria-hidden="true">*</span></label>
                                <input type="text" id="contact-name" name="name" required value="{{ old('name') }}" placeholder="Your full name">
                            </div>
                            <div class="public-conversion-form__field">
                                <label for="contact-company">Company</label>
                                <input type="text" id="contact-company" name="company" value="{{ old('company') }}" placeholder="Company name (optional)">
                            </div>
                            <div class="public-conversion-form__field">
                                <label for="contact-phone">Phone</label>
                                <input type="tel" id="contact-phone" name="phone" value="{{ old('phone') }}" placeholder="+254 700 000 000">
                            </div>
                            <div class="public-conversion-form__field">
                                <label for="contact-email">Email <span aria-hidden="true">*</span></label>
                                <input type="email" id="contact-email" name="email" required value="{{ old('email') }}" placeholder="you@company.com">
                            </div>
                            <div class="public-conversion-form__field public-conversion-form__field--full">
                                <label for="contact-subject">Subject <span aria-hidden="true">*</span></label>
                                <input type="text" id="contact-subject" name="subject" required value="{{ old('subject') }}" placeholder="How can we help?">
                            </div>
                            <div class="public-conversion-form__field public-conversion-form__field--full">
                                <label for="contact-message">Message <span aria-hidden="true">*</span></label>
                                <textarea id="contact-message" name="message" rows="5" required placeholder="Tell us about your enquiry">{{ old('message') }}</textarea>
                            </div>
                        </div>

                        <div class="public-conversion-form__submit">
                            <x-public.button type="submit" variant="gradient" size="lg">Send Message</x-public.button>
                        </div>

                        @if (session('contact_success'))
                            <div class="public-conversion-form__success" data-contact-success role="status">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p><strong>Thank you!</strong> {{ session('contact_success') }}</p>
                            </div>
                        @else
                            <div class="public-conversion-form__success" data-contact-success role="status" hidden>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p data-contact-success-text><strong>Thank you!</strong> <span></span></p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="mt-8 public-card public-card--soft p-8" data-animate="fade-up">
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
                    <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">Request a Quote</x-public.button>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
