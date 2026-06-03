<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

/**
 * Authorization marker for system settings governance UI.
 */
class SettingsGovernance extends Model
{
    protected $table = 'system_settings';

    public $timestamps = false;
}
