<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('courses.show', $course) }}" class="text-sm font-medium text-red-800 hover:text-red-700">← {{ $course->title }}</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-slate-900">{{ $lesson->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $lesson->duration_minutes }} minutes</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-[1fr_280px]">
        <article class="rounded-xl border border-slate-200 bg-white/85 p-6">
            @if ($lesson->video_url)
                <div class="mb-6 aspect-video overflow-hidden rounded-lg bg-slate-900">
                    <iframe class="h-full w-full" src="{{ $lesson->video_url }}" title="{{ $lesson->title }}" allowfullscreen></iframe>
                </div>
            @endif

            <div class="prose-lesson">
                {!! $lesson->content !!}
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6">
                @if ($isCompleted)
                    <span class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-800">Completed</span>
                @else
                    <form method="POST" action="{{ route('lessons.complete', [$course, $lesson]) }}">
                        @csrf
                        <button class="inline-flex items-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Mark as complete
                        </button>
                    </form>
                @endif
            </div>
        </article>

        <aside class="rounded-xl border border-slate-200 bg-white/80 p-4 h-fit">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Course outline</h2>
            <ul class="mt-3 space-y-2">
                @foreach ($course->lessons as $item)
                    <li>
                        <a href="{{ route('lessons.show', [$course, $item]) }}"
                           class="block rounded-md px-3 py-2 text-sm {{ $item->id === $lesson->id ? 'bg-red-50 font-semibold text-red-900' : 'text-slate-700 hover:bg-slate-50' }}">
                            {{ $item->title }}
                            @if (in_array($item->id, $completedIds, true))
                                <span class="ms-1 text-red-700">✓</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</x-app-layout>
