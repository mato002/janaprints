<x-admin-layout :title="__('Preview')">
    <div class="erp-card max-w-2xl">
        <h2 class="font-semibold">{{ $preview['subject'] ?? __('(no subject)') }}</h2>
        <pre class="mt-4 whitespace-pre-wrap text-sm">{{ $preview['body'] }}</pre>
    </div>
</x-admin-layout>
