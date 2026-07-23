@if (($action['type'] ?? null) === 'generate')
    <form
        method="POST"
        action="{{ route($action['route'], $run) }}"
        class="inline"
        @if ($action['needs_confirm'] ?? false)
            onsubmit="return confirm(@js(__('Regenerating will replace all existing payroll lines. Continue?')))"
        @endif
    >
        @csrf
        @if ($action['needs_confirm'] ?? false)
            <input type="hidden" name="confirm_regenerate" value="1">
        @endif
        <button type="submit" class="erp-btn-primary">{{ $action['label'] }}</button>
    </form>
@elseif (($action['type'] ?? null) === 'post')
    <form
        method="POST"
        action="{{ route($action['route'], $run) }}"
        class="inline"
        data-turbo-frame="erp-main"
        @if ($action['needs_confirm'] ?? false)
            onsubmit="return confirm(@js($action['confirm_message'] ?? __('Continue with this action?')))"
        @endif
    >
        @csrf
        <button
            type="submit"
            class="{{ ($action['variant'] ?? null) === 'danger' ? 'erp-btn-secondary text-red-700 border-red-200' : 'erp-btn-secondary' }}"
            @if (($action['variant'] ?? null) === 'danger' && ! ($action['needs_confirm'] ?? false))
                onclick="return confirm(@js(__('Cancel this payroll run?')))"
            @endif
        >{{ $action['label'] }}</button>
    </form>
@endif
