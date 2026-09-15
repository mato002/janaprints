@php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="$section === 'hub' ? __('System Settings') : $sectionMeta['label']"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ...($section !== 'hub' ? [['label' => $sectionMeta['label']]] : []),
    ]"
    :use-workspace-navigation="! $embedded"
>
    @if ($section === 'hub')
        @include('admin.settings.partials.hub-control-center', [
            'controlCenter' => $controlCenter,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])
    @else
        @unless ($embedded)
            @include('admin.settings.partials.hub-toolbar', [
                'title' => $sectionMeta['label'],
                'description' => $sectionMeta['description'] ?? __('Configure platform behaviour for your organization.'),
                'backUrl' => $hubBackUrl,
            ])
        @endunless

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.show', $section),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
            'branchLabel' => __('Branch context'),
            'branchEmptyLabel' => __('Company default only'),
        ])

        <x-admin.card>
            @if ($canManage)
                <form
                    id="settings-save-form"
                    method="POST"
                    action="{{ route('admin.settings.update', $section) }}"
                    class="space-y-6"
                    data-turbo="false"
                    data-settings-show-url="{{ route('admin.settings.show', WorkspaceEmbed::queryParams([
                        'section' => $section,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ])) }}"
                >
                    @csrf
                    @if ($embedded)
                        <input type="hidden" name="embedded" value="1">
                    @endif
                    <input type="hidden" name="company_id" value="{{ $companyId }}">
                    @if ($branchId)
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif

                    @if ($errors->any())
                        <div class="rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('status'))
                        <p class="text-sm font-medium text-emerald-700">{{ session('status') }}</p>
                    @endif

                    @include('admin.settings.partials.settings-table', ['editable' => true])

                    <div class="border-t border-erp-border pt-6">
                        <button
                            type="button"
                            id="settings-save-button"
                            class="erp-btn erp-btn-primary"
                            onclick="(async (btn) => {
                                const form = document.getElementById('settings-save-form');
                                const say = (m, v) => window.showErpSweetAlert ? window.showErpSweetAlert(m, v) : alert(m);
                                if (! form) { say(@json(__('Save form is missing. Refresh the page.')), 'error'); return; }
                                const label = btn.textContent;
                                btn.disabled = true;
                                btn.textContent = @json(__('Saving…'));
                                try {
                                    const res = await fetch(form.action, {
                                        method: 'POST',
                                        body: new FormData(form),
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                        credentials: 'same-origin'
                                    });
                                    const data = await res.json().catch(() => ({}));
                                    if (! res.ok) {
                                        const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : @json(__('Unable to save settings. Please try again.')));
                                        say(msg, 'error');
                                        return;
                                    }
                                    say(data.message || @json(__('Settings saved.')), 'success');
                                    const next = data.redirect || form.getAttribute('data-settings-show-url');
                                    const frame = document.getElementById('module-workspace-content');
                                    if (frame && window.Turbo && next) {
                                        window.Turbo.visit(next, { frame: 'module-workspace-content' });
                                    } else if (next) {
                                        window.location.assign(next);
                                    } else {
                                        window.location.reload();
                                    }
                                } catch (e) {
                                    say(@json(__('Unable to save settings. Please try again.')), 'error');
                                } finally {
                                    btn.disabled = false;
                                    btn.textContent = label;
                                }
                            })(this)"
                        >
                            {{ __('Save settings') }}
                        </button>
                    </div>
                </form>
            @else
                @include('admin.settings.partials.settings-table', ['editable' => false])
            @endif
        </x-admin.card>
    @endif
</x-admin-layout>
