<?php

namespace App\Support\Production;

use App\Models\Production\PrintProductTemplate;

class PrintProductTemplatePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(PrintProductTemplate $template): array
    {
        return [
            'id' => $template->id,
            'code' => $template->code,
            'name' => $template->name,
            'category' => $template->category?->value,
            'category_label' => $template->category?->label(),
            'description' => $template->description,
            'is_active' => $template->is_active,
            'revision_number' => $template->revision_number,
            'sections' => [
                'general' => $this->pairs([
                    __('Code') => $template->code,
                    __('Name') => $template->name,
                    __('Category') => $template->category?->label(),
                    __('Production type') => $template->production_type?->value
                        ? str_replace('_', ' ', ucfirst($template->production_type->value))
                        : null,
                    __('Status') => $template->is_active ? __('Active') : __('Inactive'),
                ]),
                'manufacturing' => $this->pairs([
                    __('Paper') => $template->defaultPaperInventoryItem?->item_name,
                    __('Material') => $template->defaultMaterialInventoryItem?->item_name,
                    __('GSM') => $template->gsm,
                    __('Size') => $template->default_size,
                    __('Finished size') => $template->default_finished_size,
                    __('Sheet size') => $template->default_sheet_size,
                    __('Orientation') => $template->default_orientation,
                    __('Colour mode') => $template->default_colour_mode,
                    __('Number of colours') => $template->number_of_colours,
                    __('Sides') => $template->default_sides,
                    __('Binding') => $template->default_binding_type,
                    __('Finishing') => $template->default_finishing_type,
                    __('Ups') => $template->default_ups,
                    __('Waste %') => $template->default_waste_allowance_percent,
                ]),
                'finishing_options' => $this->finishingFlags($template),
                'artwork' => $this->pairs([
                    __('Artwork required') => $template->artwork_required ? __('Yes') : __('No'),
                    __('Bleed required') => $template->bleed_required ? __('Yes') : __('No'),
                    __('Safe margin') => $template->safe_margin,
                    __('Resolution') => $template->resolution_recommendation,
                ]),
                'routing' => $this->pairs([
                    __('Preferred work center') => $template->preferredWorkCenter?->name,
                    __('Preferred machine') => $template->preferredMachineAsset?->asset_name,
                    __('Operator skill') => $template->preferred_operator_skill,
                    __('Outsource optional') => $template->optional_outsource ? __('Yes') : __('No'),
                    __('Recommended packaging') => $template->recommended_packaging,
                ]),
                'notes' => $this->pairs([
                    __('Default notes') => $template->default_notes,
                ]),
            ],
            'specification_defaults' => app(PrintProductTemplateService::class)
                ->applyToSpecificationDefaults($template),
        ];
    }

    /**
     * @param  array<string, mixed>  $pairs
     * @return list<array{label: string, value: mixed}>
     */
    protected function pairs(array $pairs): array
    {
        return collect($pairs)
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value ?? '—'])
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function finishingFlags(PrintProductTemplate $template): array
    {
        $flags = collect([
            'default_lamination' => __('Lamination'),
            'default_foiling' => __('Foiling'),
            'default_spot_uv' => __('Spot UV'),
            'default_embossing' => __('Embossing'),
            'default_debossing' => __('Debossing'),
            'default_die_cutting' => __('Die cutting'),
            'default_creasing' => __('Creasing'),
            'default_perforation' => __('Perforation'),
            'default_numbering_required' => __('Numbering'),
            'default_eyelets' => __('Eyelets'),
        ])->filter(fn ($label, $key) => (bool) $template->{$key});

        if ($flags->isEmpty()) {
            return [['label' => __('Options'), 'value' => __('None')]];
        }

        return [['label' => __('Options'), 'value' => $flags->values()->implode(', ')]];
    }
}
