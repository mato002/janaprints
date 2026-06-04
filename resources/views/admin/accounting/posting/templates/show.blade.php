<x-admin-layout :title="$template->name" :breadcrumbs="[
    ['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')],
    ['label' => __('Posting templates'), 'url' => route('admin.accounting.posting.templates.index')],
    ['label' => $template->code],
]">
    <x-admin.page-header :title="$template->name" :description="$template->description">
        <x-slot name="meta">
            <span class="erp-badge">{{ $template->module->label() }}</span>
            @if ($template->is_system)<span class="erp-badge">{{ __('System') }}</span>@endif
        </x-slot>
    </x-admin.page-header>

    <div class="erp-card">
        <h2 class="erp-card-title">{{ __('Template lines') }}</h2>
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Side') }}</th>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Description') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($template->lines as $line)
                        <tr>
                            <td class="text-sm">{{ $line->line_number }}</td>
                            <td class="text-sm capitalize">{{ $line->entry_side->value }}</td>
                            <td class="text-sm">
                                {{ $line->account_resolver->label() }}
                                @if ($line->account_key)
                                    <span class="font-mono text-xs text-slate-500">({{ $line->account_key }})</span>
                                @endif
                                @if ($line->glAccount)
                                    — {{ $line->glAccount->code }} {{ $line->glAccount->name }}
                                @endif
                            </td>
                            <td class="text-sm">{{ $line->amount_source->label() }}</td>
                            <td class="text-sm text-slate-600">{{ $line->line_description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
