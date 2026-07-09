@php
    $lesson = $lesson ?? null;
    $selectedType = old('content_type', $lesson->content_type ?? 'text');
@endphp

<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $lesson->title ?? '')" required />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div>
    <x-input-label for="content_type" value="Lesson type" />
    <select id="content_type" name="content_type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700" required>
        @foreach ([
            'text' => 'Text / article',
            'video' => 'Video',
            'image' => 'Image',
            'pdf' => 'PDF document',
            'quiz' => 'Quiz',
            'assessment' => 'Assessment',
        ] as $value => $label)
            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('content_type')" class="mt-2" />
</div>

<div data-lesson-panel="text quiz assessment">
    <x-input-label for="content" value="Instructions / content (HTML allowed)" />
    <textarea id="content" name="content" rows="8" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">{{ old('content', $lesson->content ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('content')" class="mt-2" />
</div>

<div data-lesson-panel="video" class="space-y-4">
    <div>
        <x-input-label for="media_file" value="Upload video file (MP4, WebM, MOV — max 100MB)" />
        <input id="media_file" name="media_file" type="file" accept="video/mp4,video/webm,video/quicktime"
               class="mt-1 block w-full text-sm text-slate-600 file:me-4 file:rounded-md file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-800 hover:file:bg-red-100">
        @if ($lesson?->media_path && $lesson->content_type === 'video')
            <p class="mt-2 text-sm text-slate-600">Current file: <a href="{{ $lesson->mediaUrl() }}" class="text-red-800 hover:underline" target="_blank">View uploaded video</a></p>
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remove_media" value="1" class="rounded border-slate-300 text-red-800 focus:ring-red-700">
                Remove uploaded video
            </label>
        @endif
        <x-input-error :messages="$errors->get('media_file')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="video_url" value="Or embed video URL (YouTube, Vimeo, etc.)" />
        <x-text-input id="video_url" name="video_url" class="mt-1 block w-full" :value="old('video_url', $lesson->video_url ?? '')" />
        <x-input-error :messages="$errors->get('video_url')" class="mt-2" />
    </div>
</div>

<div data-lesson-panel="image">
    <x-input-label for="media_file_image" value="Upload image (JPG, PNG, GIF, WebP — max 5MB)" />
    <input id="media_file_image" name="media_file" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
           class="mt-1 block w-full text-sm text-slate-600 file:me-4 file:rounded-md file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-800 hover:file:bg-red-100">
    @if ($lesson?->media_path && $lesson->content_type === 'image')
        <img src="{{ $lesson->mediaUrl() }}" alt="Current lesson image" class="mt-3 max-h-48 rounded-lg border border-slate-200">
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="remove_media" value="1" class="rounded border-slate-300 text-red-800 focus:ring-red-700">
            Remove uploaded image
        </label>
    @endif
    <x-input-error :messages="$errors->get('media_file')" class="mt-2" />
</div>

<div data-lesson-panel="pdf">
    <x-input-label for="media_file_pdf" value="Upload PDF (max 20MB)" />
    <input id="media_file_pdf" name="media_file" type="file" accept="application/pdf"
           class="mt-1 block w-full text-sm text-slate-600 file:me-4 file:rounded-md file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-red-800 hover:file:bg-red-100">
    @if ($lesson?->media_path && $lesson->content_type === 'pdf')
        <p class="mt-2 text-sm text-slate-600">Current file: <a href="{{ $lesson->mediaUrl() }}" class="text-red-800 hover:underline" target="_blank">View PDF</a></p>
        <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="remove_media" value="1" class="rounded border-slate-300 text-red-800 focus:ring-red-700">
            Remove uploaded PDF
        </label>
    @endif
    <x-input-error :messages="$errors->get('media_file')" class="mt-2" />
</div>

<div data-lesson-panel="quiz assessment" class="rounded-lg border border-red-100 bg-red-50/60 p-4 text-sm text-slate-700">
    After saving, you will be taken to the quiz/assessment builder to add questions and set a passing score.
    @if ($lesson?->hasQuiz())
        <p class="mt-2">
            <a href="{{ route('instructor.quizzes.edit', [$lesson->course, $lesson]) }}" class="font-semibold text-red-800 hover:underline">
                Edit questions →
            </a>
        </p>
    @endif
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="duration_minutes" value="Duration (minutes)" />
        <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="0" class="mt-1 block w-full" :value="old('duration_minutes', $lesson->duration_minutes ?? 0)" />
    </div>
    @if ($lesson)
        <div>
            <x-input-label for="sort_order" value="Sort order" />
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', $lesson->sort_order)" />
        </div>
    @endif
</div>

<label class="inline-flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-red-800 focus:ring-red-700"
           @checked(old('is_published', $lesson->is_published ?? true))>
    Published
</label>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const typeSelect = document.getElementById('content_type');
        const panels = document.querySelectorAll('[data-lesson-panel]');

        function syncPanels() {
            const type = typeSelect.value;
            panels.forEach((panel) => {
                const types = panel.dataset.lessonPanel.split(' ');
                panel.classList.toggle('hidden', !types.includes(type));
            });
        }

        typeSelect.addEventListener('change', syncPanels);
        syncPanels();
    });
</script>
