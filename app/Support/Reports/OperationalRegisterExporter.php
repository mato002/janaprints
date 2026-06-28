<?php

namespace App\Support\Reports;

use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalRegisterExporter
{
    public function __construct(
        protected OperationalRegisterScopeResolver $scopeResolver,
        protected OperationalRegisterPresenter $presenter,
        protected TabularExportWriter $writer,
    ) {}

    public function download(Request $request, string $format): StreamedResponse
    {
        $resolved = $this->scopeResolver->resolve($request);
        $tabData = $this->presenter->presentRegister(
            $resolved['scope'],
            $resolved['register'],
            $request->user(),
        );

        $rows = [
            [__('Register'), $resolved['register']],
            [__('From'), $resolved['scope']->fromDate],
            [__('To'), $resolved['scope']->toDate],
            ['', ''],
        ];

        if (! empty($tabData['summary'])) {
            $rows[] = [__('Summary'), ''];
            foreach ($tabData['summary'] as $item) {
                $rows[] = [$item['label'] ?? '', $item['value'] ?? ''];
            }
            $rows[] = ['', ''];
        }

        $table = $tabData['table'] ?? [];
        $rows[] = [$table['title'] ?? __('Register'), ''];
        $rows[] = $table['columns'] ?? [];

        foreach ($table['rows'] ?? [] as $row) {
            $values = $row['values'] ?? (array) $row;
            $rows[] = array_map('strval', $values);
        }

        if (! empty($table['totals'])) {
            $rows[] = array_map('strval', $table['totals']);
        }

        return $this->writer->downloadRawRows(
            $format,
            'operational-register-'.$resolved['register'].'-'.now()->format('Y-m-d'),
            $rows,
            __($resolved['register']),
            $resolved['scope']->fromDate.' — '.$resolved['scope']->toDate,
        );
    }
}
