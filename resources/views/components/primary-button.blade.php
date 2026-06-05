<button {{ $attributes->merge(['type' => 'submit', 'class' => 'erp-btn erp-btn-primary']) }}>
    {{ $slot }}
</button>
