<?php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourseController::class, 'index'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])
        ->name('courses.enroll');

    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])
        ->scopeBindings()
        ->name('lessons.show');

    Route::post('/courses/{course}/lessons/{lesson}/complete', [LessonController::class, 'complete'])
        ->scopeBindings()
        ->name('lessons.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'instructor'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course:id}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course:id}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course:id}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('/courses/{course:id}/lessons/create', [InstructorLessonController::class, 'create'])->name('lessons.create');
        Route::post('/courses/{course:id}/lessons', [InstructorLessonController::class, 'store'])->name('lessons.store');
        Route::get('/courses/{course:id}/lessons/{lesson:id}/edit', [InstructorLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/courses/{course:id}/lessons/{lesson:id}', [InstructorLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/courses/{course:id}/lessons/{lesson:id}', [InstructorLessonController::class, 'destroy'])->name('lessons.destroy');
    });

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/reset-logo', [SettingController::class, 'resetLogo'])->name('settings.reset-logo');
    });

require __DIR__.'/auth.php';
