<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Course extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'description',
        'level',
        'thumbnail',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course): void {
            if (empty($course->slug)) {
                $course->slug = static::uniqueSlug($course->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->where('is_published', true);
    }

    public function progressFor(User $user): int
    {
        $total = $this->publishedLessons()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereIn('lesson_id', $this->publishedLessons()->pluck('id'))
            ->count();

        return (int) round(($completed / $total) * 100);
    }
}
