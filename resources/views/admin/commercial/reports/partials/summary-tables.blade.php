@props(['tables'])

<div class="space-y-4">
    @foreach ($tables as $table)
        <x-admin.card>
            @include('admin.commercial.reports.sales.partials.simple-table', [
                'title' => $table['title'],
                'columns' => $table['columns'],
                'rows' => $table['rows'] ?? [],
            ])
        </x-admin.card>
    @endforeach
</div>
