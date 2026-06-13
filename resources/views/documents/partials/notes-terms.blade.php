@if (! empty($notesTerms['body']))
    <div class="jp-doc__notes">
        <p class="jp-doc__notes-title">{{ $notesTerms['title'] ?? __('Notes') }}</p>
        <p class="jp-doc__notes-body">{{ $notesTerms['body'] }}</p>
    </div>
@endif
