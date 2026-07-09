<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        Enrollment::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
            ],
            [
                'enrolled_at' => now(),
            ],
        );

        return redirect()
            ->route('courses.show', $course)
            ->with('status', 'You are enrolled. Start learning!');
    }
}
