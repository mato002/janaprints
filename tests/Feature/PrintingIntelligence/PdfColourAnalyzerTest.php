<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Services\PrintingIntelligence\PdfColourAnalyzer;
use Tests\TestCase;

class PdfColourAnalyzerTest extends TestCase
{
    public function test_pdf_without_ghostscript_becomes_manual_review_safely(): void
    {
        config(['printing_intelligence.ghostscript_enabled' => false]);

        $result = app(PdfColourAnalyzer::class)->analyze(tempnam(sys_get_temp_dir(), 'fakepdf'));

        $this->assertSame('manual_review', $result['status']);
        $this->assertNotEmpty($result['warnings']);
    }

    public function test_parser_handles_sample_inkcov_output(): void
    {
        $output = <<<'TXT'
Page   1   0.11765  0.23529  0.35294  0.47059 CMYK OK
Page   2   0.05000  0.10000  0.15000  0.20000 CMYK OK
TXT;

        $pages = app(PdfColourAnalyzer::class)->parseInkcovOutput($output);

        $this->assertCount(2, $pages);
        $this->assertSame(1, $pages[0]['page_number']);
        $this->assertEqualsWithDelta(11.765, $pages[0]['cyan_coverage_percent'], 0.01);
        $this->assertEqualsWithDelta(47.059, $pages[0]['black_coverage_percent'], 0.01);
    }
}
