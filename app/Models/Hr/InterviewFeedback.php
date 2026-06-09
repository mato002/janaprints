<?php

namespace App\Models\Hr;

use App\Enums\InterviewRecommendation;
use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'interview_schedule_id',
    'rating',
    'recommendation',
    'feedback',
    'submitted_by_user_id',
    'submitted_at',
])]
class InterviewFeedback extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'recommendation' => InterviewRecommendation::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(InterviewSchedule::class, 'interview_schedule_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }
}
