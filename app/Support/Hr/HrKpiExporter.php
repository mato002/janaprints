<?php

namespace App\Support\Hr;

use Illuminate\Http\Request;

class HrKpiExporter
{
    public function __construct(
        protected HrKpiScopeResolver $scopeResolver,
        protected HrWorkforceIntelligenceService $intelligence,
    ) {}

    /**
     * @return list<array<int, string>>
     */
    public function rows(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);

        $rows = [
            [__('From'), $resolved['scope']->fromDate],
            [__('To'), $resolved['scope']->toDate],
            [__('Dimension'), $resolved['filters']['dimension']],
            ['', ''],
        ];

        return array_merge($rows, $this->intelligence->exportRows($resolved['scope']));
    }
}
