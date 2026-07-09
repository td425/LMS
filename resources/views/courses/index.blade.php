<x-app-layout>
    <div class="lms-hero border-b border-red-900/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <x-site-logo class="h-24 sm:h-28" />
            <h1 class="mt-6 max-w-2xl font-display text-2xl sm:text-3xl font-semibold tracking-tight text-red-950">
                {{ $siteName }}
            </h1>
            <p class="mt-3 max-w-2xl text-lg font-medium text-slate-700">
                Learn practical skills with courses built for real hosting.
            </p>
            <p class="mt-2 max-w-xl text-slate-600">
                Browse published courses, enroll free, and track lesson progress from any device.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Go to dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Start learning</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-red-800/30 bg-white/70 px-4 py-2.5 text-sm font-semibold text-red-900 hover:bg-white">Log in</a>
                @endauth
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <form method="GET" action="{{ route('courses.index') }}" class="mb-8 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search courses..."
                   class="rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">
            <select name="level" class="rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">
                <option value="">All levels</option>
                @foreach (['beginner', 'intermediate', 'advanced'] as $level)
                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        </form>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($courses as $course)
                <a href="{{ route('courses.show', $course) }}" class="group block rounded-xl border border-red-900/10 bg-white/80 p-5 transition hover:-translate-y-0.5 hover:border-red-700/30 hover:shadow-md">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-red-800">{{ $course->level }}</span>
                        <span class="text-xs text-slate-500">{{ $course->lessons_count }} lessons</span>
                    </div>
                    <h2 class="mt-3 font-display text-xl font-semibold text-slate-900 group-hover:text-red-900">{{ $course->title }}</h2>
                    <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $course->description }}</p>
                    <p class="mt-4 text-sm text-slate-500">By {{ $course->instructor->name }} · {{ $course->enrollments_count }} enrolled</p>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-slate-300 bg-white/60 p-10 text-center text-slate-600">
                    No published courses match your filters yet.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $courses->links() }}
        </div>
    </div>
</x-app-layout>
