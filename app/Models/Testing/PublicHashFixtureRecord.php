<?php

namespace App\Models\Testing;

use App\Models\Concerns\HasPublicHash;
use Illuminate\Database\Eloquent\Model;

/**
 * Test-only fixture model for public hash foundation tests.
 * Not registered in route_exposed_models.
 */
class PublicHashFixtureRecord extends Model
{
    use HasPublicHash;

    protected $table = 'public_hash_fixture_records';

    protected $fillable = [
        'public_id',
        'label',
    ];
}
