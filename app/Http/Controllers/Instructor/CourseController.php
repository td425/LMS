<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = $request->user()
            ->courses()
            ->withCount(['lessons', 'enrollments'])
            ->latest()
            ->paginate(10);

        return view('instructor.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('instructor.courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $course = $request->user()->courses()->create([
            ...$data,
            'slug' => Course::uniqueSlug($data['title']),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('instructor.courses.edit', $course)
            ->with('status', 'Course created.');
    }

    public function edit(Request $request, Course $course): View
    {
        $this->authorizeCourse($request, $course);

        $course->load(['lessons' => fn ($q) => $q->orderBy('sort_order')]);

        return view('instructor.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($request, $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $course->update([
            ...$data,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('status', 'Course updated.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($request, $course);
        $course->delete();

        return redirect()
            ->route('instructor.courses.index')
            ->with('status', 'Course deleted.');
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        abort_unless(
            $request->user()->id === $course->instructor_id || $request->user()->isAdmin(),
            403
        );
    }
}
