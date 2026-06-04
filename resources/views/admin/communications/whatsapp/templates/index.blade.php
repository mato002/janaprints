<x-admin-layout :title="__('WhatsApp Templates')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Templates')]]">
    @include('admin.communications.whatsapp.partials.nav')
    <x-admin.page-header :title="__('WhatsApp templates')" :description="__('Bindings to COM-1 templates — automation-ready, no auto-send yet.')">
        @can('manage', App\Models\Communications\WhatsappConversation::class)
            <x-slot:actions>
                <form method="POST" action="{{ route('admin.communications.whatsapp.templates.sync') }}">
                    @csrf
                    <button type="submit" class="erp-btn erp-btn--secondary erp-btn--sm">{{ __('Sync from COM-1') }}</button>
                </form>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>

    <div class="erp-card mb-4">
        <h2 class="erp-card-title">{{ __('Automation event mapping') }}</h2>
        <p class="text-xs text-slate-500 mb-3">{{ __('Prepared for future workflows — sending is not enabled.') }}</p>
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('ERP event') }}</th>
                    <th>{{ __('COM-1 category') }}</th>
                    <th>{{ __('Active template') }}</th>
                    <th>{{ __('Binding') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($automationMap as $row)
                    <tr>
                        <td>{{ $row['event']->label() }}</td>
                        <td>{{ $row['category_label'] }}</td>
                        <td>{{ $row['template']?->name ?? '—' }}</td>
                        <td>{{ $row['binding'] ? __('Linked') : __('Not linked') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="erp-card">
        <h2 class="erp-card-title">{{ __('Template bindings') }}</h2>
        <table class="erp-table w-full mt-2">
            <thead>
                <tr>
                    <th>{{ __('Template') }}</th>
                    <th>{{ __('Automation') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Active') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bindings as $binding)
                    <tr>
                        <td>{{ $binding->communicationTemplate->name }} <span class="text-xs text-slate-400">({{ $binding->communicationTemplate->code }})</span></td>
                        <td>{{ $binding->automation_event?->label() ?? '—' }}</td>
                        <td>{{ $binding->account?->name ?? __('Any') }}</td>
                        <td>{{ $binding->is_active ? __('Yes') : __('No') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No bindings. Sync COM-1 WhatsApp templates or create templates with channel WhatsApp.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
