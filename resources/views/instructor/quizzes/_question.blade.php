@php
    $index = $index ?? 0;
    $question = $question ?? ['type' => 'multiple_choice', 'prompt' => '', 'options' => [], 'correct_option' => 0];
    $options = $question['options'] ?? [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']];
@endphp

<div class="rounded-xl border border-slate-200 bg-white/85 p-5 space-y-4" data-question>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-semibold text-slate-700">Question {{ is_numeric($index) ? ((int) $index + 1) : '' }}</p>
        <button type="button" data-remove-question class="text-sm text-red-700 hover:text-red-900">Remove</button>
    </div>

    <div>
        <x-input-label :for="'questions_'.$index.'_prompt'" value="Question prompt" />
        <textarea id="questions_{{ $index }}_prompt" name="questions[{{ $index }}][prompt]" rows="3" required
                  class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">{{ $question['prompt'] ?? '' }}</textarea>
    </div>

    <div>
        <x-input-label :for="'questions_'.$index.'_type'" value="Question type" />
        <select id="questions_{{ $index }}_type" name="questions[{{ $index }}][type]" data-question-type
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">
            <option value="multiple_choice" @selected(($question['type'] ?? '') === 'multiple_choice')>Multiple choice</option>
            <option value="true_false" @selected(($question['type'] ?? '') === 'true_false')>True / false</option>
            <option value="short_answer" @selected(($question['type'] ?? '') === 'short_answer')>Short answer (assessment)</option>
        </select>
    </div>

    <div data-options-block class="space-y-3">
        <p class="text-sm font-medium text-slate-700">Answer options (select the correct one)</p>
        @foreach ($options as $optionIndex => $option)
            <label class="flex items-center gap-3">
                <input type="radio" name="questions[{{ $index }}][correct_option]" value="{{ $optionIndex }}"
                       @checked((int) ($question['correct_option'] ?? 0) === $optionIndex)
                       class="text-red-800 focus:ring-red-700">
                <input type="text" name="questions[{{ $index }}][options][{{ $optionIndex }}][text]"
                       value="{{ $option['text'] ?? '' }}"
                       placeholder="Option {{ $optionIndex + 1 }}"
                       class="block w-full rounded-md border-slate-300 shadow-sm focus:border-red-700 focus:ring-red-700">
            </label>
        @endforeach
    </div>

    <div data-true-false-block class="space-y-2 hidden">
        <p class="text-sm font-medium text-slate-700">Correct answer</p>
        <label class="inline-flex items-center gap-2 me-4 text-sm">
            <input type="radio" name="questions[{{ $index }}][correct_option]" value="0"
                   @checked((int) ($question['correct_option'] ?? 0) === 0)
                   class="text-red-800 focus:ring-red-700"> True
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="radio" name="questions[{{ $index }}][correct_option]" value="1"
                   @checked((int) ($question['correct_option'] ?? 0) === 1)
                   class="text-red-800 focus:ring-red-700"> False
        </label>
    </div>
</div>
