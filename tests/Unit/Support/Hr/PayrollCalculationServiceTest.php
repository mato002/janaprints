<?php

namespace Tests\Unit\Support\Hr;

use App\Support\Hr\PayrollCalculationService;
use Tests\TestCase;

class PayrollCalculationServiceTest extends TestCase
{
    private PayrollCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PayrollCalculationService::class);
    }

    public function test_calculates_kenya_statutory_deductions(): void
    {
        $gross = 50000.0;

        $nssf = $this->service->calculateNssf($gross);
        $shif = $this->service->calculateShif($gross);
        $housing = $this->service->calculateHousingLevy($gross);
        $paye = $this->service->calculatePaye($gross - $nssf);

        $this->assertSame(2160.0, $nssf);
        $this->assertSame(1375.0, $shif);
        $this->assertSame(750.0, $housing);
        $this->assertGreaterThan(0, $paye);
        $this->assertLessThan($gross, $paye);
    }

    public function test_paye_applies_personal_relief(): void
    {
        $payeLow = $this->service->calculatePaye(10000);
        $this->assertSame(0.0, $payeLow);
    }
}
