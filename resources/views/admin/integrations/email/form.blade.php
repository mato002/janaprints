@props(['setting' => null, 'providers'])

<div class="space-y-6">
    <x-admin.form-section :title="__('General')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Provider') }}</label>
                <select name="provider" class="erp-select w-full" required>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->value }}" @selected(old('provider', $setting?->provider?->value) === $provider->value)>{{ $provider->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('From Name') }}</label>
                <input type="text" name="from_name" value="{{ old('from_name', $setting?->from_name) }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label">{{ __('From Email') }}</label>
                <input type="email" name="from_email" value="{{ old('from_email', $setting?->from_email) }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label">{{ __('Reply-To Email') }}</label>
                <input type="email" name="reply_to_email" value="{{ old('reply_to_email', $setting?->reply_to_email) }}" class="erp-input w-full">
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('SMTP')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label">{{ __('Host') }}</label><input type="text" name="smtp_host" value="{{ old('smtp_host', $setting?->smtp_host) }}" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Port') }}</label><input type="number" name="smtp_port" value="{{ old('smtp_port', $setting?->smtp_port) }}" class="erp-input w-full"></div>
            <div>
                <label class="erp-label">{{ __('Encryption') }}</label>
                <select name="smtp_encryption" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    <option value="tls" @selected(old('smtp_encryption', $setting?->smtp_encryption) === 'tls')>TLS</option>
                    <option value="ssl" @selected(old('smtp_encryption', $setting?->smtp_encryption) === 'ssl')>SSL</option>
                </select>
            </div>
            <div><label class="erp-label">{{ __('Username') }}</label><input type="text" name="smtp_username" value="{{ old('smtp_username', $setting?->smtp_username) }}" class="erp-input w-full" autocomplete="off"></div>
            <div class="sm:col-span-2">
                <label class="erp-label">{{ __('Password') }}</label>
                <input type="password" name="smtp_password" class="erp-input w-full" placeholder="{{ $setting?->hasCredential('smtp_password') ? __('Leave blank to keep current') : '' }}" autocomplete="new-password">
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Mailgun')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label">{{ __('Domain') }}</label><input type="text" name="mailgun_domain" value="{{ old('mailgun_domain', $setting?->mailgun_domain) }}" class="erp-input w-full"></div>
            <div>
                <label class="erp-label">{{ __('API Key') }}</label>
                <input type="password" name="mailgun_api_key" class="erp-input w-full" placeholder="{{ $setting?->hasCredential('mailgun_api_key') ? __('Leave blank to keep current') : '' }}" autocomplete="off">
            </div>
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('SendGrid')">
        <div>
            <label class="erp-label">{{ __('API Key') }}</label>
            <input type="password" name="sendgrid_api_key" class="erp-input w-full" placeholder="{{ $setting?->hasCredential('sendgrid_api_key') ? __('Leave blank to keep current') : '' }}" autocomplete="off">
        </div>
    </x-admin.form-section>

    <x-admin.form-section :title="__('Amazon SES')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="erp-label">{{ __('Access Key') }}</label><input type="text" name="ses_access_key" value="{{ old('ses_access_key', $setting?->ses_access_key) }}" class="erp-input w-full" autocomplete="off"></div>
            <div>
                <label class="erp-label">{{ __('Secret Key') }}</label>
                <input type="password" name="ses_secret_key" class="erp-input w-full" placeholder="{{ $setting?->hasCredential('ses_secret_key') ? __('Leave blank to keep current') : '' }}" autocomplete="off">
            </div>
            <div><label class="erp-label">{{ __('Region') }}</label><input type="text" name="ses_region" value="{{ old('ses_region', $setting?->ses_region) }}" class="erp-input w-full" placeholder="us-east-1"></div>
        </div>
    </x-admin.form-section>
</div>
