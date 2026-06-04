<x-admin-layout :title="__('Email settings')">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Email accounts')" :description="__('Company, branch, and department senders — SMTP/provider config stored per account (not sent until provider connected).')" />
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('From') }}</th><th>{{ __('Reply-To') }}</th><th>{{ __('Provider') }}</th><th>{{ __('Status') }}</th></tr></thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }} @if ($account->is_default)<span class="text-xs text-erp-accent">({{ __('Default') }})</span>@endif</td>
                        <td>{{ $account->from_email }}</td>
                        <td>{{ $account->reply_to_email ?? '—' }}</td>
                        <td>{{ $account->provider->label() }}</td>
                        <td>{{ $account->status->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No accounts — a default account is created on first send.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
