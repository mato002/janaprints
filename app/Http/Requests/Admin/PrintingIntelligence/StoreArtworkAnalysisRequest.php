<?php

namespace App\Http\Requests\Admin\PrintingIntelligence;

use App\Enums\ArtworkAnalysisSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreArtworkAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('printing.artwork.analyze') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = tenant()->companyId() ?? $this->user()?->company_id;
        $maxKb = (int) config('printing_intelligence.max_artwork_upload_mb', 50) * 1024;
        $extensions = config('printing_intelligence.allowed_artwork_extensions', []);
        $mimes = config('printing_intelligence.allowed_artwork_mimes', []);

        return [
            'file' => [
                'required',
                File::types($extensions)->max($maxKb),
            ],
            'quotation_id' => [
                'nullable',
                'integer',
                Rule::exists('quotations', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'production_job_card_id' => [
                'nullable',
                'integer',
                Rule::exists('production_job_cards', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'analysis_source' => [
                'nullable',
                Rule::enum(ArtworkAnalysisSource::class),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return array_filter([
            'company_id' => tenant()->companyId() ?? $this->user()?->company_id,
            'branch_id' => $this->input('branch_id') ?? tenant()->branchId(),
            'quotation_id' => $this->input('quotation_id'),
            'production_job_card_id' => $this->input('production_job_card_id'),
            'analysis_source' => $this->input('analysis_source', ArtworkAnalysisSource::Upload->value),
            'uploaded_by' => $this->user()?->id,
        ], fn ($value) => $value !== null);
    }
}
