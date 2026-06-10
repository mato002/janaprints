<?php

namespace App\Models\Commercial;

use App\Casts\FlexibleEnumCast;
use App\Enums\CommercialPriceBookStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialPriceBook extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'code', 'description', 'currency',
        'status', 'starts_at', 'ends_at', 'is_default', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => FlexibleEnumCast::class.':'.CommercialPriceBookStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_default' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CommercialPriceBookItem::class, 'price_book_id');
    }

    public function customerAssignments(): HasMany
    {
        return $this->hasMany(CommercialCustomerPriceBook::class, 'price_book_id');
    }

    public function resolveRouteBinding($value, $field = null): Model
    {
        $field ??= $this->getRouteKeyName();

        $book = static::query()->forTenant()->where($field, $value)->first();

        if ($book === null) {
            throw (new ModelNotFoundException)->setModel(static::class, [$field => $value]);
        }

        return $book;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
