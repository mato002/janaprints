<section class="ess-card">
    <h2 class="ess-section-title">{{ __('Update profile') }}</h2>
    <p class="mb-4 text-sm text-erp-muted">{{ __('You can update personal contact details. Employment and payroll fields are managed by HR.') }}</p>

    <form method="POST" action="{{ route('ess.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="ess-label" for="photo">{{ __('Profile photo') }}</label>
            <input type="file" id="photo" name="photo" accept="image/*" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="phone">{{ __('Phone') }}</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $profile['phone']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="email">{{ __('Personal email') }}</label>
            <input type="email" id="email" name="email" value="{{ old('email', $profile['email']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="address">{{ __('Address') }}</label>
            <textarea id="address" name="address" rows="3" class="ess-input w-full">{{ old('address', $profile['address']) }}</textarea>
        </div>

        <div>
            <label class="ess-label" for="emergency_contact_name">{{ __('Emergency contact name') }}</label>
            <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $profile['emergency_contact_name']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="emergency_contact_phone">{{ __('Emergency contact phone') }}</label>
            <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $profile['emergency_contact_phone']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_name">{{ __('Next of kin name') }}</label>
            <input type="text" id="next_of_kin_name" name="next_of_kin_name" value="{{ old('next_of_kin_name', $profile['next_of_kin_name']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_phone">{{ __('Next of kin phone') }}</label>
            <input type="text" id="next_of_kin_phone" name="next_of_kin_phone" value="{{ old('next_of_kin_phone', $profile['next_of_kin_phone']) }}" class="ess-input w-full">
        </div>

        <div>
            <label class="ess-label" for="next_of_kin_relationship">{{ __('Next of kin relationship') }}</label>
            <input type="text" id="next_of_kin_relationship" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship', $profile['next_of_kin_relationship']) }}" class="ess-input w-full">
        </div>

        <button type="submit" class="ess-btn ess-btn--primary w-full">{{ __('Save profile') }}</button>
    </form>
</section>
