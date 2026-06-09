<?php

namespace App\Models\Hr;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'training_program_id',
    'employee_training_assignment_id',
    'score',
    'feedback',
    'evaluated_by_user_id',
    'evaluated_at',
])]
class TrainingEvaluation extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'evaluated_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(EmployeeTrainingAssignment::class, 'employee_training_assignment_id');
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by_user_id');
    }
}
