<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Support\LessonMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function create(Request $request, Course $course): View
    {
        $this->authorizeCourse($request, $course);

        return view('instructor.lessons.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($request, $course);

        $data = $this->validatedLessonData($request);
        $sortOrder = ((int) $course->lessons()->max('sort_order')) + 1;

        $lesson = $course->lessons()->create([
            'title' => $data['title'],
            'slug' => Lesson::uniqueSlug($course->id, $data['title']),
            'content_type' => $data['content_type'],
            'content' => $data['content'] ?? null,
            'video_url' => $data['content_type'] === Lesson::TYPE_VIDEO ? ($data['video_url'] ?? null) : null,
            'media_path' => $this->storeMediaIfPresent($request, $data['content_type']),
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'sort_order' => $sortOrder,
            'is_published' => $request->boolean('is_published', true),
        ]);

        if ($lesson->hasQuiz()) {
            $this->createEmptyQuiz($lesson);
        }

        return $this->redirectAfterSave($course, $lesson, 'Lesson added.');
    }

    public function edit(Request $request, Course $course, Lesson $lesson): View
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->load('quiz.questions.options');

        return view('instructor.lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $data = $this->validatedLessonData($request, $lesson);
        $contentType = $data['content_type'];

        $mediaPath = $lesson->media_path;
        if ($lesson->content_type !== $contentType && $mediaPath) {
            LessonMedia::delete($mediaPath);
            $mediaPath = null;
        }

        if ($request->boolean('remove_media') && $mediaPath) {
            LessonMedia::delete($mediaPath);
            $mediaPath = null;
        }

        if ($request->hasFile('media_file')) {
            LessonMedia::delete($mediaPath);
            $mediaPath = LessonMedia::store($request->file('media_file'), $contentType);
        }

        $lesson->update([
            'title' => $data['title'],
            'content_type' => $contentType,
            'content' => $data['content'] ?? null,
            'video_url' => $contentType === Lesson::TYPE_VIDEO ? ($data['video_url'] ?? null) : null,
            'media_path' => in_array($contentType, [Lesson::TYPE_VIDEO, Lesson::TYPE_IMAGE, Lesson::TYPE_PDF], true)
                ? $mediaPath
                : null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'sort_order' => $data['sort_order'] ?? $lesson->sort_order,
            'is_published' => $request->boolean('is_published'),
        ]);

        if ($lesson->hasQuiz() && ! $lesson->quiz) {
            $this->createEmptyQuiz($lesson);
        }

        if (! $lesson->hasQuiz() && $lesson->quiz) {
            $lesson->quiz->delete();
        }

        return $this->redirectAfterSave($course, $lesson->fresh(), 'Lesson updated.');
    }

    public function destroy(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        LessonMedia::delete($lesson->media_path);
        $lesson->delete();

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson deleted.');
    }

    private function validatedLessonData(Request $request, ?Lesson $lesson = null): array
    {
        $contentType = $request->input('content_type', Lesson::TYPE_TEXT);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', Rule::in(Lesson::CONTENT_TYPES)],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
            'remove_media' => ['sometimes', 'boolean'],
        ];

        if ($contentType === Lesson::TYPE_VIDEO) {
            $rules['media_file'] = ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'];
        } elseif ($contentType === Lesson::TYPE_IMAGE) {
            $rules['media_file'] = [
                $lesson?->media_path ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120',
            ];
        } elseif ($contentType === Lesson::TYPE_PDF) {
            $rules['media_file'] = [
                $lesson?->media_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ];
        }

        return $request->validate($rules);
    }

    private function storeMediaIfPresent(Request $request, string $contentType): ?string
    {
        if (! $request->hasFile('media_file')) {
            return null;
        }

        return LessonMedia::store($request->file('media_file'), $contentType);
    }

    private function createEmptyQuiz(Lesson $lesson): void
    {
        Quiz::query()->create([
            'lesson_id' => $lesson->id,
            'kind' => $lesson->content_type === Lesson::TYPE_ASSESSMENT
                ? Quiz::KIND_ASSESSMENT
                : Quiz::KIND_QUIZ,
            'title' => $lesson->title,
            'passing_score' => 70,
        ]);
    }

    private function redirectAfterSave(Course $course, Lesson $lesson, string $message): RedirectResponse
    {
        if ($lesson->hasQuiz()) {
            return redirect()
                ->route('instructor.quizzes.edit', [$course, $lesson])
                ->with('status', $message.' Add questions below.');
        }

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', $message);
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        abort_unless(
            $request->user()->id === $course->instructor_id || $request->user()->isAdmin(),
            403
        );
    }
}
