<?php

namespace App\Support\Crm;

use App\Enums\CustomerArtworkType;
use App\Models\Crm\ArtworkType;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerArtworkTypeCatalog
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public function optionsForCompany(int $companyId): array
    {
        $this->ensureDefaults($companyId);

        return ArtworkType::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (ArtworkType $type) => [
                'value' => $type->code,
                'label' => $type->name,
            ])
            ->values()
            ->all();
    }

    public function labelFor(int $companyId, ?string $code): string
    {
        $code = is_string($code) ? trim($code) : '';

        if ($code === '') {
            return '';
        }

        $name = ArtworkType::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->value('name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $enum = CustomerArtworkType::tryFrom($code);

        return $enum?->label() ?? Str::headline($code);
    }

    public function defaultCode(): string
    {
        return CustomerArtworkType::Layout->value;
    }

    /**
     * @return list<string|\Illuminate\Validation\Rules\Exists>
     */
    public function validationRules(int $companyId, bool $required = false): array
    {
        $this->ensureDefaults($companyId);

        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:40',
            Rule::exists('customer_artwork_types', 'code')
                ->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('is_active', true)),
        ];
    }

    public function create(int $companyId, string $name, bool $isActive = true): ArtworkType
    {
        $name = trim($name);
        $code = $this->uniqueCode($companyId, $name);

        return ArtworkType::query()->create([
            'company_id' => $companyId,
            'name' => $name,
            'code' => $code,
            'is_active' => $isActive,
        ]);
    }

    public function ensureDefaults(int $companyId): void
    {
        if ($companyId <= 0) {
            return;
        }

        $existing = ArtworkType::query()
            ->where('company_id', $companyId)
            ->pluck('code')
            ->all();

        foreach (CustomerArtworkType::cases() as $type) {
            if (in_array($type->value, $existing, true)) {
                continue;
            }

            ArtworkType::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $type->value,
                ],
                [
                    'name' => $type->label(),
                    'is_active' => true,
                ],
            );
        }
    }

    protected function uniqueCode(int $companyId, string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? Str::limit($base, 32, '') : 'artwork-type';
        $code = $base;
        $suffix = 2;

        while (
            ArtworkType::query()
                ->where('company_id', $companyId)
                ->where('code', $code)
                ->exists()
        ) {
            $code = Str::limit($base, 32, '').'-'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
