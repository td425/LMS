<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('courses.show', $course) }}" class="text-sm font-medium text-red-800 hover:text-red-700">← {{ $course->title }}</a>
            <h1 class="mt-2 font-display text-3xl font-bold text-slate-900">{{ $lesson->title }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $lesson->contentTypeLabel() }} · {{ $lesson->duration_minutes }} minutes</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid gap-8 lg:grid-cols-[1fr_280px]">
        <article class="rounded-xl border border-slate-200 bg-white/85 p-6">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($lesson->content_type === 'video')
                @if ($lesson->media_path)
                    <div class="mb-6 aspect-video overflow-hidden rounded-lg bg-slate-900">
                        <video class="h-full w-full" controls preload="metadata">
                            <source src="{{ $lesson->mediaUrl() }}">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @elseif ($lesson->video_url)
                    <div class="mb-6 aspect-video overflow-hidden rounded-lg bg-slate-900">
                        <iframe class="h-full w-full" src="{{ $lesson->video_url }}" title="{{ $lesson->title }}" allowfullscreen></iframe>
                    </div>
                @endif
            @elseif ($lesson->content_type === 'image' && $lesson->media_path)
                <div class="mb-6">
                    <img src="{{ $lesson->mediaUrl() }}" alt="{{ $lesson->title }}" class="max-w-full rounded-lg border border-slate-200">
                </div>
            @elseif ($lesson->content_type === 'pdf' && $lesson->media_path)
                <div class="mb-6 overflow-hidden rounded-lg border border-slate-200">
                    <iframe src="{{ $lesson->mediaUrl() }}" class="h-[70vh] w-full" title="{{ $lesson->title }}"></iframe>
                    <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                        <a href="{{ $lesson->mediaUrl() }}" target="_blank" class="text-sm font-semibold text-red-800 hover:text-red-700">
                            Download PDF
                        </a>
                    </div>
                </div>
            @endif

            @if ($lesson->content)
                <div class="prose-lesson">
                    {!! $lesson->content !!}
                </div>
            @endif

            @if ($lesson->hasQuiz() && $lesson->quiz && $lesson->quiz->questions->isNotEmpty())
                <section class="mt-8 border-t border-slate-200 pt-8">
                    <h2 class="font-display text-2xl font-semibold text-slate-900">
                        {{ $lesson->content_type === 'assessment' ? 'Assessment' : 'Quiz' }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">Passing score: {{ $lesson->quiz->passing_score }}%</p>

                    @if ($latestAttempt)
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            Latest attempt: {{ $latestAttempt->scorePercent() }}%
                            @if ($latestAttempt->passed)
                                <span class="ms-2 font-semibold text-red-800">Passed</span>
                            @else
                                <span class="ms-2 font-semibold text-slate-900">Not passed yet</span>
                            @endif
                        </div>
                    @endif

                    @if (! $passedAttempt)
                        <form method="POST" action="{{ route('lessons.quiz.submit', [$course, $lesson]) }}" class="mt-6 space-y-6">
                            @csrf
                            @foreach ($lesson->quiz->questions as $question)
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <p class="font-medium text-slate-900">{{ $loop->iteration }}. {{ $question->prompt }}</p>

                                    @if ($question->type === 'multiple_choice')
                                        <div class="mt-3 space-y-2">
                                            @foreach ($question->options as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required
                                                           class="text-red-800 focus:ring-red-700">
                                                    {{ $option->text }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($question->type === 'true_false')
                                        <div class="mt-3 space-y-2">
                                            @foreach ($question->options as $option)
                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required
                                                           class="text-red-800 focus:ring-red-700">
                                                    {{ $option->text }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <textarea name="answers[{{ $question->id }}]" rows="3" required
                                                  class="mt-3 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700"
                                                  placeholder="Your answer"></textarea>
                                    @endif
                                </div>
                            @endforeach

                            <button class="inline-flex items-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                                Submit {{ $lesson->content_type === 'assessment' ? 'assessment' : 'quiz' }}
                            </button>
                        </form>
                    @endif
                </section>
            @elseif ($lesson->hasQuiz())
                <p class="mt-6 text-sm text-slate-500">The instructor has not added questions yet.</p>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-6">
                @if ($isCompleted)
                    <span class="inline-flex items-center rounded-md bg-red-50 px-3 py-2 text-sm font-semibold text-red-800">Completed</span>
                @elseif ($canComplete)
                    <form method="POST" action="{{ route('lessons.complete', [$course, $lesson]) }}">
                        @csrf
                        <button class="inline-flex items-center rounded-md bg-red-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                            Mark as complete
                        </button>
                    </form>
                @elseif ($lesson->requiresQuizPass())
                    <p class="text-sm text-slate-600">Pass the {{ $lesson->content_type === 'assessment' ? 'assessment' : 'quiz' }} above to complete this lesson.</p>
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
                            <span class="text-xs text-slate-400">· {{ $item->contentTypeLabel() }}</span>
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
