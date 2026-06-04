<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('patch')

        <x-admin.form-section
            :title="__('Profile Information')"
            :description="__('Update your account\'s profile information and email address.')"
        >
            <div class="erp-form-field md:col-span-2">
                <x-input-label :value="__('Profile photo')" />
                <div class="mt-2 flex flex-wrap items-center gap-4">
                    @if ($avatarUrl ?? null)
                        <img src="{{ $avatarUrl }}" alt="{{ __('Profile photo') }}" class="h-16 w-16 rounded-full border border-erp-border object-cover">
                    @else
                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-erp-accent/10 text-lg font-semibold text-erp-accent">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    @endif
                    <div class="min-w-0 flex-1 space-y-2">
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
                        <p class="text-xs text-slate-500">{{ __('JPEG, PNG, or WebP. Max 2 MB.') }}</p>
                        @if ($user->avatar_path)
                            <label class="flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="remove_avatar" value="1">
                                {{ __('Remove photo') }}
                            </label>
                        @endif
                    </div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>

            <div class="erp-form-field md:col-span-2">
                <x-input-label for="name" :value="__('Name')" :required="true" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="erp-form-field md:col-span-2">
                <x-input-label for="email" :value="__('Email')" :required="true" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        <p>{{ __('Your email address is unverified.') }}</p>
                        <button form="send-verification" type="submit" class="mt-1 font-medium text-erp-accent hover:underline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-emerald-700">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </x-admin.form-section>

        <div class="flex flex-wrap items-center gap-4 border-t border-erp-border pt-6">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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
