<section>
    <x-admin.form-section
        :title="__('Roles')"
        :description="__('Security groups assigned to your account. These are managed by administrators.')"
    >
        <div class="md:col-span-2">
            @if ($roles->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No roles assigned.') }}</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <span class="inline-flex items-center rounded-full border border-erp-accent/30 bg-erp-accent/10 px-3 py-1 text-sm font-medium text-erp-accent">
                            {{ $role }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </x-admin.form-section>
</section>
