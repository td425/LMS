<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_published_courses(): void
    {
        $instructor = User::factory()->instructor()->create();

        Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Published Course',
            'slug' => 'published-course',
            'description' => 'A published course',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Draft Course',
            'slug' => 'draft-course',
            'description' => 'A draft course',
            'level' => 'beginner',
            'is_published' => false,
        ]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Published Course')
            ->assertDontSee('Draft Course');
    }

    public function test_student_can_enroll_and_complete_lesson(): void
    {
        $instructor = User::factory()->instructor()->create();
        $student = User::factory()->create();

        $course = Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'PHP Basics',
            'slug' => 'php-basics',
            'description' => 'Learn PHP',
            'level' => 'beginner',
            'is_published' => true,
        ]);

        $lesson = Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Intro',
            'slug' => 'intro',
            'content' => '<p>Hello</p>',
            'duration_minutes' => 10,
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('courses.enroll', $course))
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($student)
            ->post(route('lessons.complete', [$course, $lesson]))
            ->assertRedirect();

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'is_completed' => true,
        ]);

        $this->assertSame(100, $course->fresh()->progressFor($student));
    }

    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->post(route('instructor.courses.store'), [
                'title' => 'MySQL Course',
                'description' => 'Learn MySQL on Hostinger',
                'level' => 'intermediate',
                'is_published' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'title' => 'MySQL Course',
            'instructor_id' => $instructor->id,
            'is_published' => true,
        ]);
    }
}
