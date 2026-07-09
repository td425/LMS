<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="font-display text-3xl font-bold text-slate-900">Edit course</h1>
            <a href="{{ route('courses.show', $course) }}" class="text-sm font-semibold text-teal-800 hover:text-teal-700">View public page</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-2">
        <form method="POST" action="{{ route('instructor.courses.update', $course) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white/85 p-6 h-fit">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $course->title)" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700" required>{{ old('description', $course->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="level" value="Level" />
                <select id="level" name="level" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700" required>
                    @foreach (['beginner', 'intermediate', 'advanced'] as $level)
                        <option value="{{ $level }}" @selected(old('level', $course->level) === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-teal-800 focus:ring-teal-700" @checked(old('is_published', $course->is_published))>
                Published
            </label>
            <div class="flex flex-wrap gap-3">
                <x-primary-button>Save changes</x-primary-button>
            </div>
        </form>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-semibold text-slate-900">Lessons</h2>
                <a href="{{ route('instructor.lessons.create', $course) }}" class="text-sm font-semibold text-teal-800 hover:text-teal-700">Add lesson</a>
            </div>

            <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white/85">
                @forelse ($course->lessons as $lesson)
                    <li class="flex items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $lesson->sort_order }}. {{ $lesson->title }}</p>
                            <p class="text-xs text-slate-500">{{ $lesson->is_published ? 'Published' : 'Draft' }} · {{ $lesson->duration_minutes }} min</p>
                        </div>
                        <a href="{{ route('instructor.lessons.edit', [$course, $lesson]) }}" class="text-sm font-semibold text-teal-800">Edit</a>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500">No lessons yet.</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('instructor.courses.destroy', $course) }}" onsubmit="return confirm('Delete this course and all lessons?')">
                @csrf
                @method('DELETE')
                <x-danger-button>Delete course</x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>
