<x-layouts.client :title="__('Account')" :heading="__('Account settings')">
    <p class="client-lead">
        {{ __('Update your contact details and password. Changes sync to your customer record so your account team sees the latest information.') }}
    </p>

    <form method="POST" action="{{ route('client.account.update') }}" class="client-form">
        @csrf
        @method('PUT')

        <h2 class="client-form__section">{{ __('Contact details') }}</h2>

        <div class="client-form__group">
            <label for="name" class="client-label">{{ __('Contact name') }}</label>
            <input id="name" name="name" type="text" class="client-input" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="client-form__group">
            <label for="phone" class="client-label">{{ __('Phone') }}</label>
            <input id="phone" name="phone" type="tel" class="client-input" value="{{ old('phone', $customer->phone) }}" autocomplete="tel">
        </div>

        <div class="client-form__group">
            <label for="alternative_phone" class="client-label">{{ __('Alternative phone') }}</label>
            <input id="alternative_phone" name="alternative_phone" type="tel" class="client-input" value="{{ old('alternative_phone', $customer->alternative_phone) }}" autocomplete="tel">
        </div>

        <div class="client-form__group">
            <label for="city" class="client-label">{{ __('City') }}</label>
            <input id="city" name="city" type="text" class="client-input" value="{{ old('city', $customer->city) }}">
        </div>

        <div class="client-form__group">
            <label for="physical_address" class="client-label">{{ __('Physical address') }}</label>
            <textarea id="physical_address" name="physical_address" rows="3" class="client-input">{{ old('physical_address', $customer->physical_address) }}</textarea>
        </div>

        <div class="client-form__group">
            <label for="postal_address" class="client-label">{{ __('Postal address') }}</label>
            <input id="postal_address" name="postal_address" type="text" class="client-input" value="{{ old('postal_address', $customer->postal_address) }}">
        </div>

        <div class="client-form__group">
            <label for="website" class="client-label">{{ __('Website') }}</label>
            <input id="website" name="website" type="url" class="client-input" value="{{ old('website', $customer->website) }}" placeholder="https://">
        </div>

        <div class="client-form__group">
            <label class="client-label">{{ __('Company') }}</label>
            <input type="text" class="client-input" value="{{ $customer->company_name }}" disabled>
            <p class="client-form__hint">{{ __('Contact your account manager to change your company name.') }}</p>
        </div>

        <div class="client-form__group">
            <label class="client-label">{{ __('Login email') }}</label>
            <input type="email" class="client-input" value="{{ $user->email }}" disabled>
            <p class="client-form__hint">{{ __('Email changes are handled by your account team.') }}</p>
        </div>

        <hr class="client-divider">

        <h2 class="client-form__section">{{ __('Password') }}</h2>
        <p class="client-form__hint">{{ __('Leave blank to keep your current password.') }}</p>

        <x-client.password-field
            id="password"
            name="password"
            :label="__('New password')"
            autocomplete="new-password"
        />

        <x-client.password-field
            id="password_confirmation"
            name="password_confirmation"
            :label="__('Confirm new password')"
            autocomplete="new-password"
        />

        <button type="submit" class="client-btn">{{ __('Save changes') }}</button>
    </form>
</x-layouts.client>
