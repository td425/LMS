<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuizGrader
{
    /**
     * @param  array<int|string, mixed>  $submittedAnswers
     */
    public function grade(Quiz $quiz, User $user, array $submittedAnswers): QuizAttempt
    {
        $quiz->load(['questions.options']);

        return DB::transaction(function () use ($quiz, $user, $submittedAnswers) {
            $score = 0;
            $totalPoints = 0;

            $attempt = QuizAttempt::query()->create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'submitted_at' => now(),
            ]);

            foreach ($quiz->questions as $question) {
                if ($question->isAutoGraded()) {
                    $totalPoints++;
                }

                $submitted = $submittedAnswers[$question->id] ?? null;
                $isCorrect = null;
                $optionId = null;
                $answerText = null;

                if ($question->type === QuizQuestion::TYPE_MULTIPLE_CHOICE) {
                    $optionId = is_numeric($submitted) ? (int) $submitted : null;
                    $selected = $question->options->firstWhere('id', $optionId);
                    $isCorrect = $selected?->is_correct === true;
                    if ($isCorrect) {
                        $score++;
                    }
                } elseif ($question->type === QuizQuestion::TYPE_TRUE_FALSE) {
                    $optionId = is_numeric($submitted) ? (int) $submitted : null;
                    $selected = $question->options->firstWhere('id', $optionId);
                    $isCorrect = $selected?->is_correct === true;
                    if ($isCorrect) {
                        $score++;
                    }
                } else {
                    $answerText = is_string($submitted) ? trim($submitted) : null;
                }

                QuizAnswer::query()->create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'option_id' => $optionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                ]);
            }

            $passed = $totalPoints === 0
                ? filled(collect($submittedAnswers)->filter(fn ($value) => filled($value)))
                : (int) round(($score / $totalPoints) * 100) >= $quiz->passing_score;

            $attempt->update([
                'score' => $score,
                'total_points' => $totalPoints,
                'passed' => $passed,
            ]);

            return $attempt->fresh(['answers']);
        });
    }
}
