<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $sortOrder = ((int) $course->lessons()->max('sort_order')) + 1;

        $course->lessons()->create([
            ...$data,
            'slug' => Lesson::uniqueSlug($course->id, $data['title']),
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'sort_order' => $sortOrder,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson added.');
    }

    public function edit(Request $request, Course $course, Lesson $lesson): View
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        return view('instructor.lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $lesson->update([
            ...$data,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'sort_order' => $data['sort_order'] ?? $lesson->sort_order,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson updated.');
    }

    public function destroy(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', 'Lesson deleted.');
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        abort_unless(
            $request->user()->id === $course->instructor_id || $request->user()->isAdmin(),
            403
        );
    }
}
