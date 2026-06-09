<?php

namespace Tests\Feature\Admin;

use App\Support\Export\TabularExportWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabularExportWriterTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_supports_csv_excel_and_pdf_formats(): void
    {
        $writer = app(TabularExportWriter::class);
        $headers = ['Name', 'Value'];
        $rows = [['Alpha', '1'], ['Beta', '2']];

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $writer->download($format, 'sample-export', $headers, $rows, 'Sample Export');

            $this->assertSame(200, $response->getStatusCode());
            $this->assertNotEmpty($response->headers->get('content-disposition'));

            if ($format === 'pdf') {
                $this->assertSame('application/pdf', $response->headers->get('content-type'));
                ob_start();
                $response->sendContent();
                $binary = ob_get_clean();
                $this->assertStringStartsWith('%PDF', $binary);
            }
        }
    }
}
