<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Request $request, Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->course_id === $course->id, 404);
        abort_unless($lesson->is_published || $request->user()?->id === $course->instructor_id, 404);

        $user = $request->user();
        $isOwner = $user && ($user->id === $course->instructor_id || $user->isAdmin());
        $enrolled = $user ? $user->isEnrolledIn($course) : false;

        abort_unless($isOwner || $enrolled, 403, 'Enroll in this course to view lessons.');

        $course->load(['lessons' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')]);

        $completedIds = $user
            ? LessonProgress::query()
                ->where('user_id', $user->id)
                ->where('is_completed', true)
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->pluck('lesson_id')
                ->all()
            : [];

        $isCompleted = in_array($lesson->id, $completedIds, true);

        return view('lessons.show', compact('course', 'lesson', 'completedIds', 'isCompleted'));
    }

    public function complete(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $user = $request->user();
        abort_unless($user->isEnrolledIn($course) || $user->id === $course->instructor_id || $user->isAdmin(), 403);

        LessonProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['is_completed' => true, 'completed_at' => now()],
        );

        $total = $course->publishedLessons()->count();
        $completed = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereIn('lesson_id', $course->publishedLessons()->pluck('id'))
            ->count();

        if ($total > 0 && $completed >= $total) {
            Enrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);
        }

        return back()->with('status', 'Lesson marked as complete.');
    }
}
