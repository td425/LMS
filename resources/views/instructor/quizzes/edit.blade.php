<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('instructor.lessons.edit', [$course, $lesson]) }}" class="text-sm font-medium text-red-800 hover:text-red-700">← Edit lesson</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-slate-900">
                {{ $lesson->content_type === 'assessment' ? 'Assessment' : 'Quiz' }} builder
            </h1>
            <p class="mt-1 text-sm text-slate-600">{{ $lesson->title }}</p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('instructor.quizzes.update', [$course, $lesson]) }}" class="space-y-6" id="quiz-form">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-slate-200 bg-white/85 p-6 space-y-4">
                <div>
                    <x-input-label for="title" value="Quiz / assessment title (optional)" />
                    <x-text-input id="title" name="title" class="mt-1 block w-full" :value="old('title', $quiz->title)" />
                </div>
                <div>
                    <x-input-label for="passing_score" value="Passing score (%)" />
                    <x-text-input id="passing_score" name="passing_score" type="number" min="0" max="100" class="mt-1 block w-full max-w-xs" :value="old('passing_score', $quiz->passing_score)" required />
                    <p class="mt-1 text-xs text-slate-500">Students must reach this score on auto-graded questions to pass.</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-semibold text-slate-900">Questions</h2>
                <button type="button" id="add-question" class="text-sm font-semibold text-red-800 hover:text-red-700">+ Add question</button>
            </div>

            <div id="questions-container" class="space-y-4">
                @php
                    $oldQuestions = old('questions');
                    $questions = $oldQuestions ?? $quiz->questions->map(fn ($q) => [
                        'prompt' => $q->prompt,
                        'type' => $q->type,
                        'correct_option' => $q->options->search(fn ($o) => $o->is_correct),
                        'options' => $q->options->map(fn ($o) => ['text' => $o->text])->values()->all(),
                    ])->values()->all();
                @endphp

                @forelse ($questions as $index => $question)
                    @include('instructor.quizzes._question', ['index' => $index, 'question' => $question])
                @empty
                    @include('instructor.quizzes._question', ['index' => 0, 'question' => ['type' => 'multiple_choice', 'prompt' => '', 'options' => [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']], 'correct_option' => 0]])
                @endforelse
            </div>

            <x-input-error :messages="$errors->get('questions')" class="mt-2" />

            <div class="flex gap-3">
                <x-primary-button>Save quiz</x-primary-button>
                <a href="{{ route('instructor.courses.edit', $course) }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to course</a>
            </div>
        </form>
    </div>

    <template id="question-template">
        @include('instructor.quizzes._question', ['index' => '__INDEX__', 'question' => ['type' => 'multiple_choice', 'prompt' => '', 'options' => [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']], 'correct_option' => 0]])
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('questions-container');
            const template = document.getElementById('question-template');
            let nextIndex = container.querySelectorAll('[data-question]').length;

            document.getElementById('add-question').addEventListener('click', () => {
                const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
                container.insertAdjacentHTML('beforeend', html);
                nextIndex++;
                bindQuestion(container.lastElementChild);
            });

            function bindQuestion(questionEl) {
                const typeSelect = questionEl.querySelector('[data-question-type]');
                const optionsBlock = questionEl.querySelector('[data-options-block]');
                const trueFalseBlock = questionEl.querySelector('[data-true-false-block]');

                function syncType() {
                    const type = typeSelect.value;
                    optionsBlock.classList.toggle('hidden', type !== 'multiple_choice');
                    trueFalseBlock.classList.toggle('hidden', type !== 'true_false');
                }

                typeSelect.addEventListener('change', syncType);
                syncType();

                questionEl.querySelector('[data-remove-question]').addEventListener('click', () => {
                    if (container.querySelectorAll('[data-question]').length > 1) {
                        questionEl.remove();
                    }
                });
            }

            container.querySelectorAll('[data-question]').forEach(bindQuestion);
        });
    </script>
</x-app-layout>
