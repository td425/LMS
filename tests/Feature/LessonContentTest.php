<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LessonContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_image_lesson_with_upload(): void
    {
        Storage::fake('public');

        $instructor = User::factory()->instructor()->create();
        $course = Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Media Course',
            'slug' => 'media-course',
            'description' => 'Media uploads',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        $file = UploadedFile::fake()->create('diagram.png', 100, 'image/png');

        $this->actingAs($instructor)
            ->post(route('instructor.lessons.store', $course), [
                'title' => 'Architecture diagram',
                'content_type' => 'image',
                'content' => '<p>Study this diagram.</p>',
                'media_file' => $file,
                'duration_minutes' => 5,
                'is_published' => '1',
            ])
            ->assertRedirect(route('instructor.courses.edit', $course));

        $lesson = Lesson::query()->where('title', 'Architecture diagram')->first();

        $this->assertNotNull($lesson);
        $this->assertSame('image', $lesson->content_type);
        $this->assertNotNull($lesson->media_path);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $lesson->media_path));
    }

    public function test_instructor_can_build_quiz_and_student_can_pass(): void
    {
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->create();

        $course = Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Quiz Course',
            'slug' => 'quiz-course',
            'description' => 'Quiz testing',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Safety quiz',
            'slug' => 'safety-quiz',
            'content_type' => 'quiz',
            'content' => '<p>Answer all questions.</p>',
            'duration_minutes' => 10,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $quiz = Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'kind' => 'quiz',
            'title' => 'Safety quiz',
            'passing_score' => 70,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'type' => QuizQuestion::TYPE_MULTIPLE_CHOICE,
            'prompt' => 'What color is a stop sign?',
            'sort_order' => 1,
        ]);

        $wrong = QuizOption::query()->create([
            'question_id' => $question->id,
            'text' => 'Blue',
            'is_correct' => false,
        ]);

        $correct = QuizOption::query()->create([
            'question_id' => $question->id,
            'text' => 'Red',
            'is_correct' => true,
        ]);

        $this->actingAs($student)->post(route('courses.enroll', $course));

        $this->actingAs($student)
            ->post(route('lessons.quiz.submit', [$course, $lesson]), [
                'answers' => [$question->id => $correct->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'is_completed' => true,
        ]);

        $this->actingAs($student)
            ->post(route('lessons.quiz.submit', [$course, $lesson]), [
                'answers' => [$question->id => $wrong->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'passed' => false,
        ]);
    }

    public function test_student_cannot_complete_quiz_lesson_without_passing(): void
    {
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->create();

        $course = Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Gated Quiz',
            'slug' => 'gated-quiz',
            'description' => 'Must pass quiz',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Final quiz',
            'slug' => 'final-quiz',
            'content_type' => 'quiz',
            'content' => '<p>Pass required.</p>',
            'duration_minutes' => 5,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'kind' => 'quiz',
            'passing_score' => 80,
        ]);

        $this->actingAs($student)->post(route('courses.enroll', $course));

        $this->actingAs($student)
            ->post(route('lessons.complete', [$course, $lesson]))
            ->assertForbidden();
    }

    public function test_instructor_can_save_quiz_questions_via_builder(): void
    {
        $instructor = User::factory()->instructor()->create();

        $course = Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Builder Course',
            'slug' => 'builder-course',
            'description' => 'Quiz builder',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Module quiz',
            'slug' => 'module-quiz',
            'content_type' => 'quiz',
            'duration_minutes' => 5,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'kind' => 'quiz',
            'passing_score' => 60,
        ]);

        $this->actingAs($instructor)
            ->put(route('instructor.quizzes.update', [$course, $lesson]), [
                'title' => 'Module quiz',
                'passing_score' => 60,
                'questions' => [
                    [
                        'prompt' => '2 + 2 = ?',
                        'type' => 'multiple_choice',
                        'correct_option' => 1,
                        'options' => [
                            ['text' => '3'],
                            ['text' => '4'],
                            ['text' => '5'],
                        ],
                    ],
                    [
                        'prompt' => 'The sky is blue.',
                        'type' => 'true_false',
                        'correct_option' => 0,
                    ],
                ],
            ])
            ->assertRedirect(route('instructor.quizzes.edit', [$course, $lesson]));

        $this->assertDatabaseCount('quiz_questions', 2);
        $this->assertDatabaseHas('quiz_questions', [
            'prompt' => '2 + 2 = ?',
            'type' => 'multiple_choice',
        ]);
    }
}
