<?php

namespace App\Support\MasterData;

class MasterDataRegistry
{
    /**
     * @return array<string, array{label: string, module: string, module_label: string, dependencies: list<array<string, string>>}>
     */
    public function categories(): array
    {
        $categories = [];

        foreach (config('master_data_registry.modules', []) as $moduleKey => $module) {
            foreach ($module['categories'] ?? [] as $categoryKey => $category) {
                $categories[$categoryKey] = [
                    'label' => $category['label'],
                    'module' => $moduleKey,
                    'module_label' => $module['label'],
                    'dependencies' => $category['dependencies'] ?? [],
                ];
            }
        }

        return $categories;
    }

    public function categoryLabel(string $categoryKey): string
    {
        return $this->categories()[$categoryKey]['label'] ?? str($categoryKey)->replace('_', ' ')->title()->toString();
    }

    public function moduleLabel(string $categoryKey): string
    {
        return $this->categories()[$categoryKey]['module_label'] ?? __('General');
    }

    /**
     * @return list<array{value: string, label: string, module: string}>
     */
    public function categoryOptions(): array
    {
        return collect($this->categories())
            ->map(fn (array $category, string $key) => [
                'value' => $key,
                'label' => $category['label'],
                'module' => $category['module_label'],
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    public function isValidCategory(string $categoryKey): bool
    {
        return array_key_exists($categoryKey, $this->categories());
    }

    /**
     * @return list<array<string, string>>
     */
    public function dependenciesFor(string $categoryKey): array
    {
        return $this->categories()[$categoryKey]['dependencies'] ?? [];
    }
}
