<x-app-layout>
    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold text-slate-900">Create course</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('instructor.courses.store') }}" class="space-y-5 rounded-xl border border-slate-200 bg-white/85 p-6">
            @csrf
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title')" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700" required>{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="level" value="Level" />
                <select id="level" name="level" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-700 focus:ring-teal-700" required>
                    @foreach (['beginner', 'intermediate', 'advanced'] as $level)
                        <option value="{{ $level }}" @selected(old('level', 'beginner') === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-teal-800 focus:ring-teal-700" @checked(old('is_published'))>
                Publish immediately
            </label>
            <div class="flex gap-3">
                <x-primary-button>Create course</x-primary-button>
                <a href="{{ route('instructor.courses.index') }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
