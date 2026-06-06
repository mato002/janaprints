@php
    $inquiryTypes = config('conversion.inquiry_types');
@endphp

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
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
            <input type="text" id="contact-name" name="name" required autocomplete="name" value="{{ old('name') }}" placeholder="Your full name">
        </div>
        <div class="public-conversion-form__field">
            <label for="contact-company">Company</label>
            <input type="text" id="contact-company" name="company" autocomplete="organization" value="{{ old('company') }}" placeholder="Company name (optional)">
        </div>
        <div class="public-conversion-form__field">
            <label for="contact-phone">Phone</label>
            <input type="tel" id="contact-phone" name="phone" autocomplete="tel" value="{{ old('phone') }}" placeholder="+254 700 000 000">
        </div>
        <div class="public-conversion-form__field">
            <label for="contact-email">Email <span aria-hidden="true">*</span></label>
            <input type="email" id="contact-email" name="email" required autocomplete="email" value="{{ old('email') }}" placeholder="you@company.com">
        </div>
        <div class="public-conversion-form__field public-conversion-form__field--full">
            <label for="contact-inquiry-type">Inquiry Type <span aria-hidden="true">*</span></label>
            <select id="contact-inquiry-type" name="subject" required data-contact-inquiry-type>
                <option value="" disabled {{ old('subject') ? '' : 'selected' }}>Select inquiry type</option>
                @foreach ($inquiryTypes as $type)
                    <option value="{{ $type['value'] }}" data-inquiry-slug="{{ $type['slug'] }}" @selected(old('subject') === $type['value'])>
                        {{ $type['value'] }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-brand-text-muted" data-contact-quote-hint hidden>
                For detailed pricing, use our
                <a href="{{ $quoteFormHref }}" class="font-semibold text-brand-magenta hover:text-brand-magenta-hover">quote request form</a>.
            </p>
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
