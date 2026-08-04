<dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
    @foreach ($fields as $field)
        <div class="flex justify-between gap-4 px-3 py-2.5">
            <dt class="shrink-0 text-slate-500">{{ $field['label'] }}</dt>
            <dd class="text-right font-medium text-slate-900">{{ $field['value'] ?? '—' }}</dd>
        </div>
    @endforeach
</dl>
