<?php

namespace App\Models;

use App\Support\LessonMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Lesson extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_VIDEO = 'video';

    public const TYPE_IMAGE = 'image';

    public const TYPE_PDF = 'pdf';

    public const TYPE_QUIZ = 'quiz';

    public const TYPE_ASSESSMENT = 'assessment';

    public const CONTENT_TYPES = [
        self::TYPE_TEXT,
        self::TYPE_VIDEO,
        self::TYPE_IMAGE,
        self::TYPE_PDF,
        self::TYPE_QUIZ,
        self::TYPE_ASSESSMENT,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'content_type',
        'content',
        'video_url',
        'media_path',
        'duration_minutes',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson): void {
            if (empty($lesson->slug)) {
                $lesson->slug = static::uniqueSlug($lesson->course_id, $lesson->title);
            }
        });

        static::deleting(function (Lesson $lesson): void {
            LessonMedia::delete($lesson->media_path);
        });
    }

    public static function uniqueSlug(int $courseId, string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (static::where('course_id', $courseId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function isCompletedBy(User $user): bool
    {
        return $this->progress()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->exists();
    }

    public function hasQuiz(): bool
    {
        return in_array($this->content_type, [self::TYPE_QUIZ, self::TYPE_ASSESSMENT], true);
    }

    public function requiresQuizPass(): bool
    {
        return $this->hasQuiz();
    }

    public function mediaUrl(): ?string
    {
        if (! $this->media_path) {
            return null;
        }

        return asset($this->media_path);
    }

    public function contentTypeLabel(): string
    {
        return match ($this->content_type) {
            self::TYPE_VIDEO => 'Video',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_PDF => 'PDF',
            self::TYPE_QUIZ => 'Quiz',
            self::TYPE_ASSESSMENT => 'Assessment',
            default => 'Text',
        };
    }
}
