<?php

namespace App\Models;

use App\Enums\WebsiteGalleryCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteGalleryItem extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'category',
        'description',
        'location',
        'quantity_label',
        'timeline_label',
        'image_path',
        'alt_text',
        'is_featured',
        'is_published',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WebsiteGalleryItem $item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }

            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title);
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

    public function categoryEnum(): ?WebsiteGalleryCategory
    {
        return WebsiteGalleryCategory::tryFrom($this->category);
    }

    public function categoryLabel(): string
    {
        return $this->categoryEnum()?->label() ?? Str::headline(str_replace('-', ' ', $this->category));
    }

    public function publicImageUrl(): string
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, '/')) {
            return $this->image_path;
        }

        // Relative path — works on localhost, 127.0.0.1, and production regardless of APP_URL.
        return '/storage/'.$this->image_path;
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
