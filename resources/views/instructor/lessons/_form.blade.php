@php($lesson = $lesson ?? null)

<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $lesson->title ?? '')" required />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div>
    <x-input-label for="content" value="Content (HTML allowed)" />
    <textarea id="content" name="content" rows="8" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">{{ old('content', $lesson->content ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('content')" class="mt-2" />
</div>

<div>
    <x-input-label for="video_url" value="Video URL (optional)" />
    <x-text-input id="video_url" name="video_url" class="mt-1 block w-full" :value="old('video_url', $lesson->video_url ?? '')" />
    <x-input-error :messages="$errors->get('video_url')" class="mt-2" />
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
