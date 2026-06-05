<x-admin-layout :title="__('Generate API Key')">
    <x-admin.page-header :title="__('Generate API key')" />
    <form method="POST" action="{{ route('admin.integrations.api-keys.store') }}" class="max-w-xl space-y-4">
        @csrf
        <div><label class="erp-label">{{ __('Name') }}</label><input type="text" name="name" class="erp-input w-full" required></div>
        <div><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3"></textarea></div>
        <div>
            <label class="erp-label">{{ __('Environment') }}</label>
            <select name="environment" class="erp-select w-full" required>
                @foreach ($environments as $env)
                    <option value="{{ $env->value }}">{{ $env->label() }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="erp-label">{{ __('Allowed IPs') }}</label><input type="text" name="allowed_ips" class="erp-input w-full" placeholder="192.168.1.1, 10.0.0.0/8"></div>
        <button type="submit" class="erp-btn-primary">{{ __('Generate key') }}</button>
    </form>
</x-admin-layout>
