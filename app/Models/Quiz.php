<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    public const KIND_QUIZ = 'quiz';

    public const KIND_ASSESSMENT = 'assessment';

    protected $fillable = [
        'lesson_id',
        'kind',
        'title',
        'passing_score',
    ];

    protected function casts(): array
    {
        return [
            'passing_score' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function latestPassedAttempt(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->latest('submitted_at')
            ->first();
    }

    public function isAssessment(): bool
    {
        return $this->kind === self::KIND_ASSESSMENT;
    }
}
