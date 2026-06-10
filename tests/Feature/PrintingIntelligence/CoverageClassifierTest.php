<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\CoverageClass;
use App\Support\PrintingIntelligence\CoverageClassifier;
use Tests\TestCase;

class CoverageClassifierTest extends TestCase
{
    protected CoverageClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = app(CoverageClassifier::class);
    }

    public function test_classifies_low_medium_high_full(): void
    {
        $this->assertSame(CoverageClass::Low, $this->classifier->coverageClassForTotal(10));
        $this->assertSame(CoverageClass::Medium, $this->classifier->coverageClassForTotal(35));
        $this->assertSame(CoverageClass::High, $this->classifier->coverageClassForTotal(65));
        $this->assertSame(CoverageClass::Full, $this->classifier->coverageClassForTotal(90));
        $this->assertSame(CoverageClass::Unknown, $this->classifier->coverageClassForTotal(null));
    }

    public function test_emits_heavy_coverage_warnings(): void
    {
        $result = $this->classifier->classify([
            'cmyk_coverage_percent' => 85,
            'black_coverage_percent' => 20,
        ]);

        $this->assertContains('high_ink_coverage', $result['warnings']);
        $this->assertSame(CoverageClass::Full, $result['coverage_class']);
    }

    public function test_emits_black_heavy_warning(): void
    {
        $result = $this->classifier->classify([
            'cmyk_coverage_percent' => 75,
            'black_coverage_percent' => 80,
        ]);

        $this->assertContains('mostly_black', $result['warnings']);
    }

    public function test_uses_inked_area_for_image_style_metrics(): void
    {
        $result = $this->classifier->classify([
            'rgb_coverage_percent' => 100,
            'cmyk_coverage_percent' => 25,
            'black_coverage_percent' => 100,
        ]);

        $this->assertSame(CoverageClass::Full, $result['coverage_class']);
    }

    public function test_uses_max_plate_for_pdf_style_metrics(): void
    {
        $result = $this->classifier->classify([
            'rgb_coverage_percent' => 25,
            'cmyk_coverage_percent' => 25,
            'black_coverage_percent' => 100,
        ]);

        $this->assertSame(CoverageClass::Full, $result['coverage_class']);
    }
}
