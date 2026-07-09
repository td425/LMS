<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.lessons'])
            ->latest('enrolled_at')
            ->get();

        $teachingCourses = $user->isInstructor()
            ? $user->courses()->withCount(['lessons', 'enrollments'])->latest()->get()
            : collect();

        return view('dashboard', [
            'enrollments' => $enrollments,
            'teachingCourses' => $teachingCourses,
        ]);
    }
}
