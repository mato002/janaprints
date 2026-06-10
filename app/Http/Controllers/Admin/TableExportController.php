<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableExportController extends Controller
{
    public function __invoke(Request $request, TabularExportWriter $writer): StreamedResponse
    {
        $payload = $this->resolvePayload($request);

        return $writer->download(
            $payload['format'],
            $payload['basename'].'-'.now()->format('Y-m-d'),
            $payload['headers'],
            $payload['rows'],
            $payload['title'] ?? $payload['basename'],
        );
    }

    /**
     * @return array{format: string, basename: string, title: string|null, headers: list<string>, rows: list<list<string>>}
     */
    protected function resolvePayload(Request $request): array
    {
        if (is_string($request->input('headers'))) {
            $request->merge([
                'headers' => json_decode($request->string('headers')->toString(), true) ?? [],
                'rows' => json_decode($request->string('rows')->toString(), true) ?? [],
            ]);
        }

        return $this->validatePayload($request);
    }

    /**
     * @return array{format: string, basename: string, title: string|null, headers: list<string>, rows: list<list<string>>}
     */
    protected function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'format' => ['required', 'in:csv,excel,pdf'],
            'basename' => ['required', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:200'],
            'headers' => ['required', 'array', 'max:50'],
            'headers.*' => ['string', 'max:500'],
            'rows' => ['present', 'array', 'max:10000'],
            'rows.*' => ['array', 'max:50'],
            'rows.*.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $headers = array_map(
            fn ($header) => trim(strip_tags((string) $header)),
            $validated['headers'],
        );

        $rows = collect($validated['rows'])
            ->map(fn (array $row) => collect($row)
                ->map(fn ($cell) => trim(preg_replace('/\s+/u', ' ', strip_tags((string) $cell)) ?? ''))
                ->values()
                ->all())
            ->values()
            ->all();

        $basename = Str::slug($validated['basename']) ?: 'export';

        return [
            'format' => $validated['format'],
            'basename' => $basename,
            'title' => filled($validated['title'] ?? null) ? (string) $validated['title'] : null,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
