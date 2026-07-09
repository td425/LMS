<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-red-800">{{ $course->level }}</p>
                <h1 class="font-display text-3xl font-bold text-slate-900">{{ $course->title }}</h1>
                <p class="mt-1 text-sm text-slate-600">Instructor: {{ $course->instructor->name }}</p>
            </div>
            @if ($enrolled)
                <div class="text-sm font-medium text-red-900">Progress: {{ $progress }}%</div>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-[2fr_1fr]">
        <section>
            <p class="text-slate-700 leading-relaxed">{{ $course->description }}</p>

            @if ($enrolled)
                <div class="mt-6 h-2 overflow-hidden rounded-full bg-red-100">
                    <div class="h-full rounded-full bg-red-700 transition-all" style="width: {{ $progress }}%"></div>
                </div>
            @endif

            <h2 class="mt-10 font-display text-2xl font-semibold text-slate-900">Lessons</h2>
            <ol class="mt-4 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white/80">
                @forelse ($course->lessons as $index => $lesson)
                    <li class="flex items-center justify-between gap-4 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $index + 1 }}. {{ $lesson->title }}</p>
                            <p class="text-xs text-slate-500">{{ $lesson->duration_minutes }} min</p>
                        </div>
                        @if ($enrolled || (auth()->check() && (auth()->id() === $course->instructor_id || auth()->user()->isAdmin())))
                            <a href="{{ route('lessons.show', [$course, $lesson]) }}" class="text-sm font-semibold text-red-800 hover:text-red-700">Open</a>
                        @else
                            <span class="text-xs text-slate-400">Enroll to open</span>
                        @endif
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-slate-500">No lessons published yet.</li>
                @endforelse
            </ol>
        </section>

        <aside class="rounded-xl border border-red-900/10 bg-white/80 p-5 h-fit">
            <h3 class="font-display text-lg font-semibold text-slate-900">Enrollment</h3>
            @auth
                @if ($enrolled)
                    <p class="mt-2 text-sm text-slate-600">You are enrolled in this course.</p>
                    @if ($course->lessons->isNotEmpty())
                        <a href="{{ route('lessons.show', [$course, $course->lessons->first()]) }}"
                           class="mt-4 inline-flex w-full justify-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Continue learning
                        </a>
                    @endif
                @else
                    <p class="mt-2 text-sm text-slate-600">Join free and track your lesson progress.</p>
                    <form method="POST" action="{{ route('courses.enroll', $course) }}" class="mt-4">
                        @csrf
                        <button class="inline-flex w-full justify-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Enroll now
                        </button>
                    </form>
                @endif
            @else
                <p class="mt-2 text-sm text-slate-600">Create an account to enroll and start learning.</p>
                <a href="{{ route('register') }}" class="mt-4 inline-flex w-full justify-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Register</a>
                <a href="{{ route('login') }}" class="mt-2 inline-flex w-full justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Log in</a>
            @endauth
        </aside>
    </div>
</x-app-layout>
