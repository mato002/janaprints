<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <x-admin.form-section
            :title="__('Update Password')"
            :description="__('Ensure your account is using a long, random password to stay secure.')"
        >
            <div class="erp-form-field md:col-span-2">
                <x-input-label for="update_password_current_password" :value="__('Current Password')" :required="true" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div class="erp-form-field">
                <x-input-label for="update_password_password" :value="__('New Password')" :required="true" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="erp-form-field">
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" :required="true" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </x-admin.form-section>

        <div class="flex flex-wrap items-center gap-4 border-t border-erp-border pt-6">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
