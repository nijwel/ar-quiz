{{-- Exam questions. Rendered only after the attempt has started (see UserAnswerController::start). --}}
<div class="grid gap-6 lg:grid-cols-[1fr_15rem]">
    <form action="{{ route('user.quiz.submit') }}" method="POST" id="quizForm" class="space-y-5">
        @csrf
        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

        @foreach ($questions as $key => $question)
            <div id="q-{{ $question->id }}" class="card scroll-mt-44 p-5 sm:p-6">
                <div class="mb-5 flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-white/10 font-display text-sm font-bold text-volt-400">
                        {{ str_pad($key + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h2 class="pt-1 text-base font-semibold text-white sm:text-lg">{{ $question->question }}</h2>
                </div>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    @foreach ($question->answers as $answer)
                        <label
                            class="group flex cursor-pointer items-center gap-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10 transition hover:bg-white/10 hover:ring-brand-500/40 has-checked:bg-brand-500/10 has-checked:ring-2 has-checked:ring-brand-500">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}"
                                data-question="{{ $question->id }}" class="peer sr-only">
                            <span
                                class="grid size-8 shrink-0 place-items-center rounded-lg bg-white/5 text-sm font-bold text-ink-400 ring-1 ring-white/10 transition peer-checked:bg-brand-600 peer-checked:text-snow peer-checked:ring-brand-600">
                                {{ chr(64 + $loop->iteration) }}
                            </span>
                            <span class="text-sm font-medium text-ink-200 peer-checked:text-brand-300">{{ $answer->answer }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="card flex flex-col items-center gap-4 p-6 text-center sm:flex-row sm:text-left">
            <div class="flex-1">
                <p class="font-display text-lg font-bold text-white">সব উত্তর দেওয়া শেষ?</p>
                <p class="text-sm text-ink-400">জমা দেওয়ার আগে একবার চেক করে নিন।</p>
            </div>
            <button class="btn btn-primary btn-lg w-full sm:w-auto" type="submit">
                <span class="icon-[tabler--send] size-5"></span> Submit Answers
            </button>
        </div>
    </form>

    {{-- Navigator --}}
    <aside class="hidden lg:block">
        <div class="card sticky top-52 p-5">
            <p class="mb-3 text-xs font-bold tracking-wider text-ink-400 uppercase">Questions</p>
            <div class="grid grid-cols-5 gap-2">
                @foreach ($questions as $key => $question)
                    <a href="#q-{{ $question->id }}" data-nav="{{ $question->id }}"
                        class="grid aspect-square place-items-center rounded-lg bg-white/5 text-xs font-bold text-ink-400 ring-1 ring-white/10 transition hover:ring-brand-400">
                        {{ $key + 1 }}
                    </a>
                @endforeach
            </div>
            <div class="mt-4 flex items-center gap-4 text-xs text-ink-400">
                <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-brand-600"></span> Answered</span>
                <span class="flex items-center gap-1.5"><span class="size-3 rounded bg-white/10 ring-1 ring-white/10"></span> Left</span>
            </div>
        </div>
    </aside>
</div>
