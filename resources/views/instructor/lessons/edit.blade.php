<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold text-slate-900">Edit lesson</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $course->title }}</p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <form method="POST" action="{{ route('instructor.lessons.update', [$course, $lesson]) }}" class="space-y-5 rounded-xl border border-slate-200 bg-white/85 p-6">
            @csrf
            @method('PUT')
            @include('instructor.lessons._form', ['lesson' => $lesson])
            <div class="flex gap-3">
                <x-primary-button>Save lesson</x-primary-button>
                <a href="{{ route('instructor.courses.edit', $course) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
            </div>
        </form>

        <form method="POST" action="{{ route('instructor.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Delete this lesson?')">
            @csrf
            @method('DELETE')
            <x-danger-button>Delete lesson</x-danger-button>
        </form>
    </div>
</x-app-layout>
