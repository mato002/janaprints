@props(['setting' => null, 'providers'])

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="erp-label">{{ __('Provider') }}</label>
        <select name="provider" class="erp-select w-full" required>
            @foreach ($providers as $provider)
                <option value="{{ $provider->value }}" @selected(old('provider', $setting?->provider?->value) === $provider->value)>{{ $provider->label() }}</option>
            @endforeach
        </select>
    </div>
    <div><label class="erp-label">{{ __('Sender ID') }}</label><input type="text" name="sender_id" value="{{ old('sender_id', $setting?->sender_id) }}" class="erp-input w-full"></div>
    <div class="sm:col-span-2"><label class="erp-label">{{ __('API URL') }}</label><input type="url" name="api_url" value="{{ old('api_url', $setting?->api_url) }}" class="erp-input w-full"></div>
    <div>
        <label class="erp-label">{{ __('API Key') }}</label>
        <input type="password" name="api_key" class="erp-input w-full" placeholder="{{ $setting?->api_key ? __('Leave blank to keep current') : '' }}" autocomplete="off">
    </div>
    <div><label class="erp-label">{{ __('Username') }}</label><input type="text" name="username" value="{{ old('username', $setting?->username) }}" class="erp-input w-full" autocomplete="off"></div>
    <div>
        <label class="erp-label">{{ __('Password') }}</label>
        <input type="password" name="password" class="erp-input w-full" placeholder="{{ $setting?->password ? __('Leave blank to keep current') : '' }}" autocomplete="off">
    </div>
    <div class="sm:col-span-2"><label class="erp-label">{{ __('Callback URL') }}</label><input type="url" name="callback_url" value="{{ old('callback_url', $setting?->callback_url) }}" class="erp-input w-full"></div>
</div>
