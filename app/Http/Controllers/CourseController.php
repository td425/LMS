<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->with('instructor')
            ->withCount(['lessons', 'enrollments'])
            ->where('is_published', true)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('level'), fn ($query) => $query->where('level', $request->string('level')))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function show(Request $request, Course $course): View
    {
        abort_unless($course->is_published || ($request->user()?->id === $course->instructor_id) || $request->user()?->isAdmin(), 404);

        $course->load(['instructor', 'lessons' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')]);

        $enrolled = $request->user() ? $request->user()->isEnrolledIn($course) : false;
        $progress = ($request->user() && $enrolled) ? $course->progressFor($request->user()) : 0;

        return view('courses.show', compact('course', 'enrolled', 'progress'));
    }
}
