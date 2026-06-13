<table class="jp-doc__bottom-row" cellpadding="0" cellspacing="0">
    <tr>
        <td class="jp-doc__bottom-notes">
            @include('documents.partials.notes-terms', ['notesTerms' => $notesTerms ?? []])
        </td>
        <td class="jp-doc__bottom-totals">
            @include('documents.partials.totals', ['totals' => $totals ?? []])
        </td>
    </tr>
</table>
