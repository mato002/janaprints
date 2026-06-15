<?php

namespace App\Models\EmailIdentity;

use App\Enums\EmailIdentity\MailboxStatus;
use App\Enums\EmailIdentity\MailboxType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'email_address',
    'local_part',
    'domain',
    'type',
    'status',
    'department_key',
    'system_key',
    'provisioned_at',
    'provision_error',
    'metadata',
])]
class CorporateMailbox extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'type' => MailboxType::class,
            'status' => MailboxStatus::class,
            'provisioned_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
