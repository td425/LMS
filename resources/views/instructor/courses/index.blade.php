<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h1 class="font-display text-3xl font-bold text-slate-900">Instructor courses</h1>
            <a href="{{ route('instructor.courses.create') }}" class="inline-flex items-center rounded-md bg-teal-800 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-700">New course</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white/80">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Level</th>
                        <th class="px-4 py-3 font-medium">Lessons</th>
                        <th class="px-4 py-3 font-medium">Students</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($courses as $course)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $course->title }}</td>
                            <td class="px-4 py-3 capitalize">{{ $course->level }}</td>
                            <td class="px-4 py-3">{{ $course->lessons_count }}</td>
                            <td class="px-4 py-3">{{ $course->enrollments_count }}</td>
                            <td class="px-4 py-3">{{ $course->is_published ? 'Published' : 'Draft' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="font-semibold text-teal-800 hover:text-teal-700">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-slate-500">No courses yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $courses->links() }}</div>
    </div>
</x-app-layout>
