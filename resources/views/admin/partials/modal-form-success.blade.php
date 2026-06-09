<turbo-frame id="erp-form-modal">
    <div
        data-erp-modal-success
        data-message="{{ $message }}"
        data-refresh="{{ ($refresh ?? true) ? '1' : '0' }}"
        hidden
    ></div>
</turbo-frame>
