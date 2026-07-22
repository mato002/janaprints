<section x-data="{ q: '' }">
    <x-admin.form-section
        :title="__('Permissions')"
        :description="__('Effective access rights from your roles. Read-only on this page.')"
    >
        <div class="md:col-span-2 space-y-4">
            @if ($permissions->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No permissions assigned.') }}</p>
            @else
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">
                        {{ __(':count permissions', ['count' => $permissions->count()]) }}
                    </p>
                    <input
                        type="search"
                        x-model.debounce.150ms="q"
                        class="erp-input w-full sm:max-w-xs"
                        placeholder="{{ __('Filter permissions…') }}"
                        aria-label="{{ __('Filter permissions') }}"
                    >
                </div>

                <div class="max-h-80 space-y-4 overflow-y-auto pr-1">
                    @foreach ($permissionsByModule as $module => $modulePermissions)
                        <div
                            data-permission-module="{{ $module }}"
                            x-show="!q.trim() || Array.from($el.querySelectorAll('[data-permission]')).some((node) => node.dataset.permission.includes(q.trim().toLowerCase())) || $el.dataset.permissionModule.includes(q.trim().toLowerCase())"
                        >
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $module }}</h3>
                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($modulePermissions as $permission)
                                    <li
                                        data-permission="{{ strtolower($permission) }}"
                                        x-show="!q.trim() || $el.dataset.permission.includes(q.trim().toLowerCase())"
                                        class="rounded-md border border-erp-border bg-white px-2 py-1 font-mono text-[11px] text-slate-700"
                                    >{{ $permission }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-admin.form-section>
</section>
