<?php

namespace Database\Seeders;

use App\Enums\TaxCategoryType;
use App\Enums\TaxDirection;
use App\Enums\TaxDocumentType;
use App\Models\Company;
use App\Models\Tax\TaxCategory;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxPeriod;
use App\Models\Tax\TaxRate;
use App\Models\Tax\TaxRule;
use Illuminate\Database\Seeder;

class JanaPrintsTaxSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        if (TaxCode::query()->where('company_id', $company->id)->exists()) {
            $this->command?->warn('Tax codes already seeded for JANA.');

            return;
        }

        $categories = [
            ['code' => 'VAT-OUT', 'name' => 'Output VAT', 'type' => TaxCategoryType::Vat, 'direction' => TaxDirection::Output],
            ['code' => 'VAT-IN', 'name' => 'Input VAT', 'type' => TaxCategoryType::Vat, 'direction' => TaxDirection::Input],
            ['code' => 'VAT-ZR', 'name' => 'Zero Rated', 'type' => TaxCategoryType::ZeroRated, 'direction' => TaxDirection::Output],
            ['code' => 'VAT-EX', 'name' => 'Exempt', 'type' => TaxCategoryType::Exempt, 'direction' => TaxDirection::Output],
            ['code' => 'WHT', 'name' => 'Withholding Tax', 'type' => TaxCategoryType::WithholdingTax, 'direction' => TaxDirection::Input],
        ];

        $categoryModels = [];
        foreach ($categories as $index => $row) {
            $categoryModels[$row['code']] = TaxCategory::query()->create([
                'company_id' => $company->id,
                ...$row,
                'sort_order' => $index + 1,
            ]);
        }

        $codes = [
            ['code' => 'VAT16', 'name' => 'VAT 16%', 'category' => 'VAT-OUT', 'rate' => 16.0],
            ['code' => 'VAT16-IN', 'name' => 'VAT 16% (Purchases)', 'category' => 'VAT-IN', 'rate' => 16.0],
            ['code' => 'ZR', 'name' => 'Zero Rated', 'category' => 'VAT-ZR', 'rate' => 0.0],
            ['code' => 'EXEMPT', 'name' => 'Exempt', 'category' => 'VAT-EX', 'rate' => 0.0],
            ['code' => 'WHT5', 'name' => 'WHT 5%', 'category' => 'WHT', 'rate' => 5.0],
        ];

        $codeModels = [];
        foreach ($codes as $index => $row) {
            $taxCode = TaxCode::query()->create([
                'company_id' => $company->id,
                'tax_category_id' => $categoryModels[$row['category']]->id,
                'code' => $row['code'],
                'name' => $row['name'],
                'sort_order' => $index + 1,
            ]);

            TaxRate::query()->create([
                'tax_code_id' => $taxCode->id,
                'rate_percent' => $row['rate'],
                'effective_from' => '2020-01-01',
                'is_active' => true,
            ]);

            $codeModels[$row['code']] = $taxCode;
        }

        $rules = [
            [TaxDocumentType::CustomerInvoice, 'VAT16'],
            [TaxDocumentType::CustomerCreditNote, 'VAT16'],
            [TaxDocumentType::SupplierBill, 'VAT16-IN'],
            [TaxDocumentType::SupplierCreditNote, 'VAT16-IN'],
            [TaxDocumentType::SupplierPayment, 'WHT5'],
        ];

        foreach ($rules as [$docType, $code]) {
            TaxRule::query()->create([
                'company_id' => $company->id,
                'tax_code_id' => $codeModels[$code]->id,
                'document_type' => $docType,
                'is_default' => true,
                'priority' => 10,
            ]);
        }

        $currentPeriod = \App\Models\Accounting\AccountingPeriod::query()
            ->where('company_id', $company->id)
            ->where('is_current', true)
            ->first();

        if ($currentPeriod) {
            TaxPeriod::query()->create([
                'company_id' => $company->id,
                'code' => $currentPeriod->code,
                'name' => __('Tax :period', ['period' => $currentPeriod->name]),
                'start_date' => $currentPeriod->start_date,
                'end_date' => $currentPeriod->end_date,
            ]);
        }
    }
}
