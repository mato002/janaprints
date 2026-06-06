@php
    $services = config('conversion.services');
@endphp

<section
    id="quote-form"
    class="public-quote-section public-section public-dot-pattern"
    data-reveal-section
    aria-label="Request a quote"
>
    <div class="public-container">
        <div class="public-quote-section__intro" data-animate="fade-up">
            <x-public.section-heading
                badge="Request a Quote"
                title="Get Your Free Quotation"
                description="Tell us about your project and our team will respond with pricing and guidance."
                align="center"
                class="!mb-0 max-w-3xl"
            />
        </div>

        <div class="public-quote-section__card public-card public-card--soft public-card--static" data-animate="fade-up" data-animate-delay="1">
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">{{ __('Please correct the following and try again:') }}</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                class="public-conversion-form public-conversion-form--light"
                data-quote-form
                method="POST"
                action="{{ route('public.quote-requests.store') }}"
                enctype="multipart/form-data"
                novalidate
            >
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">
                <input type="text" name="_gotcha" value="" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">

                <div class="public-conversion-form__grid">
                    <div class="public-conversion-form__field">
                        <label for="quote-name">Name <span aria-hidden="true">*</span></label>
                        <input type="text" id="quote-name" name="name" required autocomplete="name" placeholder="Your full name" value="{{ old('name') }}">
                    </div>
                    <div class="public-conversion-form__field">
                        <label for="quote-company">Company</label>
                        <input type="text" id="quote-company" name="company" autocomplete="organization" placeholder="Company name (optional)" value="{{ old('company') }}">
                    </div>
                    <div class="public-conversion-form__field">
                        <label for="quote-phone">Phone <span aria-hidden="true">*</span></label>
                        <input type="tel" id="quote-phone" name="phone" required autocomplete="tel" placeholder="+254 700 000 000" value="{{ old('phone') }}">
                    </div>
                    <div class="public-conversion-form__field">
                        <label for="quote-email">Email <span aria-hidden="true">*</span></label>
                        <input type="email" id="quote-email" name="email" required autocomplete="email" placeholder="you@company.com" value="{{ old('email') }}">
                    </div>
                    <div class="public-conversion-form__field">
                        <label for="quote-service">Service Needed <span aria-hidden="true">*</span></label>
                        <select id="quote-service" name="service" required>
                            <option value="">Select a service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service }}" @selected(old('service', old('service_needed')) === $service)>{{ $service }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="public-conversion-form__field">
                        <label for="quote-quantity">Quantity</label>
                        <input type="text" id="quote-quantity" name="quantity" placeholder="e.g. 500 business cards" value="{{ old('quantity') }}">
                    </div>
                    <div class="public-conversion-form__field public-conversion-form__field--full">
                        <label for="quote-deadline">Deadline</label>
                        <input type="text" id="quote-deadline" name="deadline" placeholder="When do you need this delivered?" value="{{ old('deadline') }}">
                    </div>
                    <div class="public-conversion-form__field public-conversion-form__field--full">
                        <label for="quote-message">Message <span aria-hidden="true">*</span></label>
                        <textarea id="quote-message" name="message" rows="4" required placeholder="Share project details, dimensions, materials, or special requirements">{{ old('message') }}</textarea>
                    </div>
                    <div class="public-conversion-form__field public-conversion-form__field--full">
                        <label>Artwork Upload</label>
                        <div class="public-conversion-form__upload" data-upload-placeholder>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <p>Drag &amp; drop artwork files here, or click to browse</p>
                            <span>PDF, AI, EPS, PSD, JPG, PNG, SVG — max 25 MB</span>
                            <input type="file" name="artwork" accept=".pdf,.ai,.eps,.psd,.jpg,.jpeg,.png,.svg" class="sr-only" data-artwork-input>
                        </div>
                        <p class="public-conversion-form__upload-note">Optional — attach artwork for faster quoting.</p>
                    </div>
                </div>

                <div class="public-conversion-form__submit public-conversion-form__submit--quote">
                    <x-public.button type="submit" variant="gradient" size="lg" class="w-full sm:w-auto">
                        Submit Quote Request
                    </x-public.button>
                </div>

                @if (session('quote_success'))
                    <div class="public-conversion-form__success" data-quote-success role="status">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p><strong>Thank you!</strong> {{ session('quote_success') }}</p>
                    </div>
                @else
                    <div class="public-conversion-form__success" data-quote-success role="status" hidden>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p data-quote-success-text><strong>Thank you!</strong> <span></span></p>
                    </div>
                @endif
            </form>
        </div>
    </div>
</section>
