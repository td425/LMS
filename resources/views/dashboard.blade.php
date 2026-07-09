<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        <section>
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-2xl font-semibold text-slate-900">My learning</h2>
                <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-red-800 hover:text-red-700">Browse courses</a>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($enrollments as $enrollment)
                    @php($course = $enrollment->course)
                    @php($progress = $course->progressFor(auth()->user()))
                    <a href="{{ route('courses.show', $course) }}" class="rounded-xl border border-slate-200 bg-white/80 p-5 hover:border-red-700/30 hover:shadow-sm">
                        <h3 class="font-display text-lg font-semibold text-slate-900">{{ $course->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ ucfirst($course->level) }} · {{ $course->lessons->count() }} lessons</p>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-red-100">
                            <div class="h-full rounded-full bg-red-700" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="mt-2 text-sm font-medium text-red-900">{{ $progress }}% complete</p>
                    </a>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-slate-300 bg-white/60 p-8 text-slate-600">
                        You are not enrolled in any courses yet.
                        <a href="{{ route('courses.index') }}" class="ms-1 font-semibold text-red-800">Find a course</a>
                    </div>
                @endforelse
            </div>
        </section>

        @if (auth()->user()->isAdmin())
            <section>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Admin</h2>
                    <a href="{{ route('admin.settings.edit') }}" class="inline-flex items-center rounded-md bg-red-800 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">Site settings</a>
                </div>
                <p class="mt-3 text-sm text-slate-600">Configure the site logo, site name, and branding used across the LMS.</p>
            </section>
        @endif

        @if (auth()->user()->isInstructor())
            <section>
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-2xl font-semibold text-slate-900">Teaching</h2>
                    <a href="{{ route('instructor.courses.create') }}" class="inline-flex items-center rounded-md bg-red-800 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">New course</a>
                </div>

                <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white/80">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">Course</th>
                                <th class="px-4 py-3 font-medium">Lessons</th>
                                <th class="px-4 py-3 font-medium">Students</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($teachingCourses as $course)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $course->title }}</td>
                                    <td class="px-4 py-3">{{ $course->lessons_count }}</td>
                                    <td class="px-4 py-3">{{ $course->enrollments_count }}</td>
                                    <td class="px-4 py-3">{{ $course->is_published ? 'Published' : 'Draft' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('instructor.courses.edit', $course) }}" class="font-semibold text-red-800 hover:text-red-700">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-slate-500">No courses yet. Create your first course.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
