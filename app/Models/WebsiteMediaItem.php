<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WebsiteMediaItem extends Model
{
    protected $fillable = [
        'uuid',
        'slot_key',
        'section',
        'label',
        'image_path',
        'fallback_path',
        'alt_text',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WebsiteMediaItem $item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publicImageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, '/')) {
            return $this->image_path;
        }

        return '/storage/'.$this->image_path;
    }

    public function previewUrl(): string
    {
        return $this->publicImageUrl()
            ?? $this->fallback_path
            ?? config('public-images.default');
    }

    /**
     * @return array{label: string, variant: string, detail: string}
     */
    public function sourceStatus(): array
    {
        if (! $this->is_active) {
            return [
                'label' => __('Inactive — fallback active'),
                'variant' => 'warning',
                'detail' => __('Storefront uses config fallback while this slot is inactive.'),
            ];
        }

        if ($this->image_path) {
            return [
                'label' => __('Using uploaded CMS image'),
                'variant' => 'success',
                'detail' => __('This slot is serving the uploaded image from storage.'),
            ];
        }

        if ($this->fallback_path) {
            return [
                'label' => __('Using config fallback'),
                'variant' => 'neutral',
                'detail' => __('No upload yet — static/config fallback image is shown.'),
            ];
        }

        return [
            'label' => __('Missing fallback warning'),
            'variant' => 'danger',
            'detail' => __('No upload or fallback path — default placeholder may be used.'),
        ];
    }

    public function hasUploadedImage(): bool
    {
        return filled($this->image_path);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
