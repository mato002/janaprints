@if ($errors->has('workflow') || $errors->has('status'))
    <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        {{ $errors->first('workflow') ?: $errors->first('status') }}
    </div>
@endif
