@php
    $enumLabel = fn ($case) => str_replace('_', ' ', ucfirst($case->value));
@endphp

<x-admin-layout :title="__('New WhatsApp message')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('New message')]]">
    @include('admin.communications.whatsapp.partials.nav')

    <x-admin.page-header
        :title="__('New WhatsApp message')"
        :description="__('Pick a person by category, filter the list, search, then send.')"
    />

    <form
        method="POST"
        action="{{ route('admin.communications.whatsapp.conversations.store') }}"
        class="erp-card max-w-3xl space-y-4"
        x-data="whatsappComposeForm(@js([
            'contactType' => old('contact_type', $contactType),
            'selectedId' => old('contact_id', $selectedId),
            'phone' => old('phone_number', $defaultPhone),
            'pickerOptions' => $pickerOptions,
            'filters' => [
                'branch_id' => (string) old('filters.branch_id', ''),
                'customer_type' => (string) old('filters.customer_type', ''),
                'status' => (string) old('filters.status', ''),
                'has_outstanding' => (string) old('filters.has_outstanding', ''),
                'department_id' => (string) old('filters.department_id', ''),
                'employment_status' => (string) old('filters.employment_status', ''),
                'vendor_type' => (string) old('filters.vendor_type', ''),
            ],
        ]))"
    >
        @csrf

        <input type="hidden" name="contact_type" :value="contactType">
        <input type="hidden" name="contact_id" :value="selectedId || ''">

        <div>
            <label class="erp-label">{{ __('Who are you messaging?') }}</label>
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach ([
                    'customers' => __('Customers'),
                    'leads' => __('Leads'),
                    'employees' => __('Employees'),
                    'suppliers' => __('Suppliers'),
                    'phone' => __('Phone only'),
                ] as $value => $label)
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="contactType === '{{ $value }}' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        @click="setContactType('{{ $value }}')"
                    >{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2" x-show="contactType !== 'phone'" x-cloak>
            <div class="space-y-2 rounded-lg border border-erp-border p-3 sm:col-span-2">
                <p class="text-xs font-semibold text-slate-500">{{ __('Filters') }}</p>
                <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                    <div x-show="['customers', 'leads'].includes(contactType)" x-cloak>
                        <label class="erp-label text-xs">{{ __('Branch') }}</label>
                        <select class="erp-input w-full" x-model="filters.branch_id" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Customer type') }}</label>
                        <select class="erp-input w-full" x-model="filters.customer_type" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\CustomerType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $enumLabel($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Customer status') }}</label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\CustomerStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Outstanding') }}</label>
                        <select class="erp-input w-full" x-model="filters.has_outstanding" @change="onFiltersChanged()">
                            <option value="">{{ __('Any') }}</option>
                            <option value="1">{{ __('Has outstanding') }}</option>
                        </select>
                    </div>

                    <div x-show="contactType === 'leads'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Lead status') }}</label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\LeadStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="contactType === 'employees'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Department') }}</label>
                        <select class="erp-input w-full" x-model="filters.department_id" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="contactType === 'employees'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Employment status') }}</label>
                        <select class="erp-input w-full" x-model="filters.employment_status" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\EmploymentStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="contactType === 'suppliers'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Supplier type') }}</label>
                        <select class="erp-input w-full" x-model="filters.vendor_type" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\VendorType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $enumLabel($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="contactType === 'suppliers'" x-cloak>
                        <label class="erp-label text-xs">{{ __('Supplier status') }}</label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\VendorStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $enumLabel($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="space-y-2 rounded-lg border border-erp-border p-3 sm:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-500">{{ __('Search & pick') }}</p>
                    <p class="text-xs text-slate-500">
                        <span x-text="visibleContacts.length"></span> {{ __('shown') }}
                    </p>
                </div>
                <input
                    type="search"
                    class="erp-input w-full"
                    placeholder="{{ __('Type a name or phone…') }}"
                    x-model="contactSearch"
                    autocomplete="off"
                >
                <div class="max-h-52 overflow-y-auto rounded-md border border-erp-border divide-y divide-erp-border bg-white">
                    <template x-for="person in visibleContacts" :key="contactType + '-' + person.id">
                        <button
                            type="button"
                            class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50"
                            :class="String(selectedId) === String(person.id) ? 'bg-emerald-50' : ''"
                            @click="selectContact(person)"
                        >
                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-erp-border"
                                  :class="String(selectedId) === String(person.id) ? 'border-erp-accent bg-erp-accent' : ''">
                                <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="String(selectedId) === String(person.id)"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium text-erp-primary" x-text="person.label"></span>
                                <span class="block font-mono text-xs text-slate-500" x-text="person.phone"></span>
                            </span>
                        </button>
                    </template>
                    <p x-show="visibleContacts.length === 0" class="px-3 py-4 text-center text-xs text-slate-500">
                        {{ __('No matches — adjust filters or search.') }}
                    </p>
                </div>
                @error('contact_id')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="erp-label">{{ __('WhatsApp phone') }}</label>
            <input
                type="text"
                name="phone_number"
                class="erp-input w-full"
                x-model="phone"
                placeholder="2547…"
                :required="contactType === 'phone'"
            >
            <p class="mt-1 text-xs text-slate-500" x-show="contactType === 'phone'" x-cloak>
                {{ __('Enter any number in international format when possible.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500" x-show="contactType !== 'phone'" x-cloak>
                {{ __('Filled from the selected person — you can still edit it.') }}
            </p>
            @error('phone_number')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($templates->isNotEmpty())
            <div>
                <label class="erp-label">{{ __('Template (optional)') }}</label>
                <select name="whatsapp_template_id" class="erp-input w-full">
                    <option value="">{{ __('Free-form message') }}</option>
                    @foreach ($templates as $tpl)
                        <option value="{{ $tpl->id }}" @selected((int) old('whatsapp_template_id') === $tpl->id)>
                            {{ $tpl->communicationTemplate?->name ?? __('Template #:id', ['id' => $tpl->id]) }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="erp-label">{{ __('Message') }}</label>
            <textarea
                name="body"
                rows="4"
                class="erp-input w-full"
                placeholder="{{ __('Type your WhatsApp message…') }}"
            >{{ old('body') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">{{ __('Required unless you choose a template.') }}</p>
            @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Send') }}</button>
            <a href="{{ route('admin.communications.whatsapp.inbox') }}" class="erp-btn erp-btn--secondary" data-turbo-frame="erp-main">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</x-admin-layout>
