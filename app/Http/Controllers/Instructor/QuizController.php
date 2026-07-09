<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function edit(Request $request, Course $course, Lesson $lesson): View
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);
        abort_unless($lesson->hasQuiz(), 404);

        $quiz = $lesson->quiz ?? Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'kind' => $lesson->content_type === Lesson::TYPE_ASSESSMENT
                ? Quiz::KIND_ASSESSMENT
                : Quiz::KIND_QUIZ,
            'title' => $lesson->title,
            'passing_score' => 70,
        ]);

        $quiz->load(['questions.options']);

        return view('instructor.quizzes.edit', compact('course', 'lesson', 'quiz'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);
        abort_unless($lesson->hasQuiz(), 404);

        $quiz = $lesson->quiz;
        abort_unless($quiz, 404);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.prompt' => ['required', 'string', 'max:2000'],
            'questions.*.type' => ['required', Rule::in([
                QuizQuestion::TYPE_MULTIPLE_CHOICE,
                QuizQuestion::TYPE_TRUE_FALSE,
                QuizQuestion::TYPE_SHORT_ANSWER,
            ])],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.text' => ['nullable', 'string', 'max:500'],
            'questions.*.correct_option' => ['nullable', 'integer', 'min:0'],
        ]);

        $quiz->update([
            'title' => $data['title'] ?: $lesson->title,
            'passing_score' => $data['passing_score'],
        ]);

        $quiz->questions()->each(fn (QuizQuestion $question) => $question->options()->delete());
        $quiz->questions()->delete();

        foreach ($data['questions'] as $index => $questionData) {
            $question = $quiz->questions()->create([
                'type' => $questionData['type'],
                'prompt' => $questionData['prompt'],
                'sort_order' => $index + 1,
            ]);

            if ($question->type === QuizQuestion::TYPE_TRUE_FALSE) {
                $correct = (int) ($questionData['correct_option'] ?? 0);
                $question->options()->createMany([
                    ['text' => 'True', 'is_correct' => $correct === 0],
                    ['text' => 'False', 'is_correct' => $correct === 1],
                ]);
            } elseif ($question->type === QuizQuestion::TYPE_MULTIPLE_CHOICE) {
                $options = $questionData['options'] ?? [];
                $correctIndex = (int) ($questionData['correct_option'] ?? 0);

                foreach ($options as $optionIndex => $option) {
                    if (! filled($option['text'] ?? null)) {
                        continue;
                    }

                    QuizOption::query()->create([
                        'question_id' => $question->id,
                        'text' => $option['text'],
                        'is_correct' => $optionIndex === $correctIndex,
                    ]);
                }
            }
        }

        return redirect()
            ->route('instructor.quizzes.edit', [$course, $lesson])
            ->with('status', 'Quiz saved.');
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        abort_unless(
            $request->user()->id === $course->instructor_id || $request->user()->isAdmin(),
            403
        );
    }
}
