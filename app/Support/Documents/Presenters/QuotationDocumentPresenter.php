<?php

namespace App\Support\Documents\Presenters;

use App\Enums\QuotationAttachmentType;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Sales\Quotation;
use App\Support\Documents\Presenters\Concerns\BuildsDocumentBlocks;
use App\Support\Platform\FormCustomFieldService;

class QuotationDocumentPresenter
{
    use BuildsDocumentBlocks;

    public function __construct(
        protected FormCustomFieldService $customFields,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Quotation $quotation): array
    {
        $quotation->loadMissing([
            'customer',
            'company',
            'branch',
            'preparer',
            'items',
            'attachments',
        ]);

        $custom = $this->customFields->valuesFor($quotation, 'quotation');
        $currency = $quotation->currency ?: 'KES';
        [$statusLabel, $statusVariant] = $this->statusPresentation($quotation->status);

        return [
            'logoDataUri' => $this->documentsLogoDataUri($quotation->company_id),
            'documentType' => 'quotation',
            'title' => __('QUOTATION'),
            'documentNumber' => $quotation->quotation_number,
            'documentNumberLabel' => __('No.'),
            'currency' => $currency,
            'headerHighlight' => $this->headerHighlightBlock(
                __('Total'),
                $this->formatMoney((float) $quotation->total_amount, $currency),
            ),
            'status' => [
                'label' => $statusLabel,
                'variant' => $statusVariant,
            ],
            'dates' => $this->filterMetaRows([
                ['label' => __('Quote Date'), 'value' => $quotation->quotation_date?->format('d M Y')],
                ['label' => __('Valid Until'), 'value' => $quotation->valid_until?->format('d M Y')],
            ]),
            'company' => $this->companyBlock($quotation->company),
            'customer' => $this->customerBlock($quotation->customer, compact: true),
            'customerLabel' => __('Bill To'),
            'meta' => [],
            'columns' => [
                ['key' => 'index', 'label' => __('No'), 'align' => 'left', 'width' => '6%'],
                ['key' => 'description', 'label' => __('Item & Description'), 'align' => 'left', 'width' => '44%'],
                ['key' => 'quantity', 'label' => __('Qty'), 'align' => 'right', 'width' => '14%'],
                ['key' => 'rate', 'label' => __('Rate'), 'align' => 'right', 'width' => '16%'],
                ['key' => 'amount', 'label' => __('Amount'), 'align' => 'right', 'width' => '20%'],
            ],
            'items' => $this->presentItems($quotation, $currency),
            'totals' => $this->presentTotals($quotation, $currency),
            'paymentFooter' => $this->paymentFooterBlock($quotation->company_id),
            'documentFooter' => $this->documentFooterBlock($quotation->company_id),
            'notesTerms' => [
                'title' => __('Notes'),
                'body' => $this->resolveNotesTerms($quotation, $custom),
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function statusPresentation(QuotationStatus $status): array
    {
        return match ($status) {
            QuotationStatus::Draft => [__('Draft'), 'neutral'],
            QuotationStatus::PendingApproval => [__('Pending'), 'warning'],
            QuotationStatus::Sent => [__('Sent'), 'info'],
            QuotationStatus::Viewed => [__('Viewed'), 'info'],
            QuotationStatus::Accepted => [__('Accepted'), 'success'],
            QuotationStatus::Rejected => [__('Rejected'), 'danger'],
            QuotationStatus::Expired => [__('Expired'), 'warning'],
            QuotationStatus::Converted => [__('Converted'), 'success'],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    protected function presentItems(Quotation $quotation, string $currency): array
    {
        return $quotation->items->values()->map(function ($item, int $index) use ($currency) {
            $description = $item->item_name;
            if ($item->description) {
                $description .= ' — '.$item->description;
            }

            return [
                'index' => (string) ($index + 1),
                'description' => $description,
                'quantity' => number_format((float) $item->quantity, 0),
                'rate' => number_format((float) $item->unit_price, 2),
                'amount' => $this->formatMoney((float) $item->line_total, $currency),
            ];
        })->all();
    }

    /**
     * @return list<array{label: string, value: string, highlight?: bool}>
     */
    protected function presentTotals(Quotation $quotation, string $currency): array
    {
        $lines = [
            ['label' => __('Subtotal'), 'value' => $this->formatMoney((float) $quotation->subtotal, $currency)],
        ];

        if ((float) $quotation->discount_amount > 0) {
            $lines[] = [
                'label' => __('Discount'),
                'value' => $this->formatMoney((float) $quotation->discount_amount, $currency),
            ];
        }

        if ((float) $quotation->tax_amount > 0) {
            $lines[] = [
                'label' => $this->documentTaxLabel($quotation->company_id),
                'value' => $this->formatMoney((float) $quotation->tax_amount, $currency),
            ];
        }

        $lines[] = [
            'label' => __('Total'),
            'value' => $this->formatMoney((float) $quotation->total_amount, $currency),
            'highlight' => true,
            'balanceBar' => true,
        ];

        return $lines;
    }

    /**
     * @param  array<string, string|null>  $custom
     */
    protected function resolveArtworkReference(Quotation $quotation, array $custom): ?string
    {
        if (filled($custom['artwork_reference'] ?? null)) {
            return $custom['artwork_reference'];
        }

        $artworkRequest = ArtworkRequest::query()
            ->where('quotation_id', $quotation->id)
            ->orderByDesc('id')
            ->first();

        if ($artworkRequest) {
            return $artworkRequest->request_number.($artworkRequest->title ? ' — '.$artworkRequest->title : '');
        }

        $attachment = $quotation->attachments
            ->first(fn ($file) => $file->attachment_type === QuotationAttachmentType::Artwork);

        return $attachment?->original_name;
    }

    /**
     * @param  array<string, string|null>  $custom
     */
    protected function resolveNotesTerms(Quotation $quotation, array $custom): string
    {
        $parts = array_filter([
            $quotation->notes,
            $custom['terms'] ?? null,
            $this->documentTerm('quotation', $quotation->company_id),
        ]);

        return implode("\n\n", $parts);
    }

}
