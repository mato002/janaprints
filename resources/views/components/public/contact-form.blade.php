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
        <x-public.form-floating-field
            id="contact-name"
            name="name"
            label="Name"
            required
            autocomplete="name"
            maxlength="120"
        />
        <x-public.form-floating-field
            id="contact-company"
            name="company"
            label="Company"
            optional
            autocomplete="organization"
            maxlength="160"
        />
        <x-public.form-floating-field
            id="contact-phone"
            name="phone"
            label="Phone"
            type="tel"
            optional
            autocomplete="tel"
            inputmode="tel"
            maxlength="20"
        />
        <x-public.form-floating-field
            id="contact-email"
            name="email"
            label="Email"
            type="email"
            required
            autocomplete="email"
            maxlength="160"
        />

        <div @class([
            'public-field-float',
            'public-field-float--select',
            'public-conversion-form__field',
            'public-conversion-form__field--full',
            'is-filled' => filled(old('subject')),
        ])>
            <select id="contact-inquiry-type" name="subject" required data-contact-inquiry-type>
                <option value="" disabled @selected(! old('subject'))>Select inquiry type</option>
                @foreach ($inquiryTypes as $type)
                    <option value="{{ $type['value'] }}" data-inquiry-slug="{{ $type['slug'] }}" @selected(old('subject') === $type['value'])>
                        {{ $type['value'] }}
                    </option>
                @endforeach
            </select>
            <label for="contact-inquiry-type">
                Inquiry Type
                <span class="public-field-float__required" aria-hidden="true">*</span>
            </label>
            <p class="mt-2 text-xs text-brand-text-muted" data-contact-quote-hint hidden>
                For detailed pricing, use our
                <a href="{{ $quoteFormHref }}" class="font-semibold text-brand-magenta hover:text-brand-magenta-hover">quote request form</a>.
            </p>
        </div>

        <x-public.form-floating-field
            id="contact-message"
            name="message"
            label="Message"
            type="textarea"
            :rows="5"
            required
            full
            maxlength="3000"
        />
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
