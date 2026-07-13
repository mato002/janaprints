<?php

namespace App\Support\Accounting;

use App\Models\Accounting\Currency;
use App\Models\Accounting\ExchangeRate;
use Illuminate\Validation\ValidationException;

class ExchangeRateService
{
    public function getRate(int $companyId, string $currencyCode, string $date): float
    {
        $base = strtoupper((string) config('accounting.base_currency', 'KES'));
        $code = strtoupper($currencyCode);

        if ($code === $base) {
            return 1.0;
        }

        $rate = ExchangeRate::query()
            ->where('company_id', $companyId)
            ->where('currency_code', $code)
            ->whereDate('rate_date', '<=', $date)
            ->orderByDesc('rate_date')
            ->value('rate_to_base');

        if ($rate === null) {
            throw ValidationException::withMessages([
                'rate' => __('No exchange rate found for :code on or before :date.', [
                    'code' => $code,
                    'date' => $date,
                ]),
            ]);
        }

        return (float) $rate;
    }

    /**
     * Convert an amount from foreign currency into base currency.
     */
    public function convert(int $companyId, float $amount, string $fromCurrency, string $date): float
    {
        $rate = $this->getRate($companyId, $fromCurrency, $date);

        return round($amount * $rate, 2);
    }

    /**
     * Convert an amount from base currency into foreign currency.
     */
    public function convertFromBase(int $companyId, float $amount, string $toCurrency, string $date): float
    {
        $rate = $this->getRate($companyId, $toCurrency, $date);

        if ($rate <= 0) {
            throw ValidationException::withMessages([
                'rate' => __('Exchange rate must be greater than zero.'),
            ]);
        }

        return round($amount / $rate, 2);
    }

    /**
     * @param  array{currency_code: string, rate_date: string, rate_to_base: float, source?: ?string}  $data
     */
    public function storeRate(int $companyId, array $data): ExchangeRate
    {
        $code = strtoupper($data['currency_code']);

        if (! Currency::query()->where('code', $code)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'currency_code' => __('Currency :code is not active.', ['code' => $code]),
            ]);
        }

        return ExchangeRate::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'currency_code' => $code,
                'rate_date' => $data['rate_date'],
            ],
            [
                'rate_to_base' => round((float) $data['rate_to_base'], 8),
                'source' => $data['source'] ?? null,
            ],
        );
    }
}
