<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LmsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@lms.test'],
            [
                'name' => 'LMS Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ],
        );

        $instructor = User::query()->updateOrCreate(
            ['email' => 'instructor@lms.test'],
            [
                'name' => 'Amina Instructor',
                'password' => Hash::make('password'),
                'role' => User::ROLE_INSTRUCTOR,
                'email_verified_at' => now(),
            ],
        );

        $student = User::query()->updateOrCreate(
            ['email' => 'student@lms.test'],
            [
                'name' => 'Sam Student',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT,
                'email_verified_at' => now(),
            ],
        );

        $web = Course::query()->updateOrCreate(
            ['slug' => 'web-development-foundations'],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Web Development Foundations',
                'description' => 'Learn HTML, CSS, and JavaScript fundamentals to build modern websites. Perfect for beginners starting a career in web development.',
                'level' => 'beginner',
                'is_published' => true,
            ],
        );

        $laravel = Course::query()->updateOrCreate(
            ['slug' => 'laravel-for-beginners'],
            [
                'instructor_id' => $instructor->id,
                'title' => 'Laravel for Beginners',
                'description' => 'Build real PHP applications with Laravel: routing, Blade, Eloquent, authentication, and deployment to shared hosting.',
                'level' => 'intermediate',
                'is_published' => true,
            ],
        );

        $draft = Course::query()->updateOrCreate(
            ['slug' => 'advanced-mysql-patterns'],
            [
                'instructor_id' => $admin->id,
                'title' => 'Advanced MySQL Patterns',
                'description' => 'Indexing, query optimization, and schema design for production LMS workloads.',
                'level' => 'advanced',
                'is_published' => false,
            ],
        );

        $this->seedLessons($web, [
            ['Welcome & Course Setup', 'Get your editor ready and understand how this course is structured.', 8],
            ['HTML Structure', 'Semantic HTML, forms, and accessible page layouts.', 20],
            ['CSS Layouts', 'Flexbox, Grid, and responsive design basics.', 25],
            ['JavaScript Essentials', 'Variables, functions, DOM updates, and events.', 30],
        ]);

        $this->seedLessons($laravel, [
            ['Laravel Project Setup', 'Install Laravel, configure .env, and connect MySQL.', 15],
            ['Routing & Controllers', 'Map URLs to controller actions cleanly.', 20],
            ['Eloquent & Migrations', 'Model your LMS data with migrations and relationships.', 28],
            ['Auth with Breeze', 'Register, login, and protect student/instructor routes.', 22],
            ['Deploy to Hostinger', 'Upload files, set document root to public, run migrations.', 18],
        ]);

        $this->seedLessons($draft, [
            ['Query Plans', 'Read EXPLAIN output and choose better indexes.', 35],
        ]);

        Enrollment::query()->firstOrCreate(
            ['user_id' => $student->id, 'course_id' => $web->id],
            ['enrolled_at' => now()->subDays(3)],
        );

        $firstLesson = $web->lessons()->orderBy('sort_order')->first();
        if ($firstLesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $student->id, 'lesson_id' => $firstLesson->id],
                ['is_completed' => true, 'completed_at' => now()->subDay()],
            );
        }
    }

    /**
     * @param  list<array{0: string, 1: string, 2: int}>  $lessons
     */
    private function seedLessons(Course $course, array $lessons): void
    {
        foreach ($lessons as $index => [$title, $content, $minutes]) {
            Lesson::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'slug' => Str::slug($title),
                ],
                [
                    'title' => $title,
                    'content' => "<p>{$content}</p><p>Complete the practice tasks for this lesson, then mark it complete to track your progress.</p>",
                    'duration_minutes' => $minutes,
                    'sort_order' => $index + 1,
                    'is_published' => true,
                    'video_url' => null,
                ],
            );
        }
    }
}
