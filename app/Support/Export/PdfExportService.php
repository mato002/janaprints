<?php

namespace App\Support\Export;

use App\Models\Hr\PayrollPayslip;
use App\Support\Branding\BrandingAssets;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfExportService
{
    public function __construct(
        protected BrandingAssets $branding,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function downloadView(string $basename, string $view, array $data = [], string $orientation = 'portrait'): StreamedResponse
    {
        $html = view($view, array_merge($data, $this->brandingViewData()))->render();

        return $this->downloadHtml($basename, $html, $orientation);
    }

    public function downloadHtml(string $basename, string $html, string $orientation = 'portrait'): StreamedResponse
    {
        $pdf = $this->render($this->ensureBrandingHeader($html), $orientation);

        return response()->streamDownload(
            fn () => print($pdf),
            "{$basename}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function render(string $html, string $orientation = 'portrait'): string
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @return array<string, mixed>
     */
    public function brandingViewData(): array
    {
        return [
            'pdfLogoDataUri' => $this->branding->logoDataUri(),
            'pdfCompanyName' => $this->branding->companyDisplayName(),
        ];
    }

    public function renderBrandedHtml(string $html, string $orientation = 'portrait'): string
    {
        return $this->render($this->ensureBrandingHeader($html), $orientation);
    }

    /**
     * @return array<string, mixed>
     */
    public function payslipViewData(PayrollPayslip $payslip, ?\DateTimeInterface $generatedAt = null): array
    {
        return array_merge($this->brandingViewData(), [
            'payslip' => $payslip,
            'generatedAt' => $generatedAt ?? now(),
            'company' => config('documents.company'),
            'logoDataUri' => $this->branding->documentsLogoDataUri(),
        ]);
    }

    protected function ensureBrandingHeader(string $html): string
    {
        if (str_contains($html, 'data-pdf-branding-header')) {
            return $html;
        }

        $styles = view('exports.pdf-styles')->render();
        $header = view('exports.partials.pdf-header', $this->brandingViewData())->render();

        if (preg_match('/<head[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $insertAt = $matches[0][1] + strlen($matches[0][0]);

            return substr($html, 0, $insertAt).$styles.substr($html, $insertAt);
        }

        $html = $styles.$html;

        if (preg_match('/<body[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $insertAt = $matches[0][1] + strlen($matches[0][0]);

            return substr($html, 0, $insertAt).$header.substr($html, $insertAt);
        }

        return $header.$html;
    }
}
