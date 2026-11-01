@extends('layouts.app')

@section('title', 'Result · ' . $quiz->title)

@section('content')
    @php
        $total = $quiz->questions->count();
        $percent = $total ? round(($rightAnswers / $total) * 100) : 0;
        [$verdict, $verdictTone] = match (true) {
            $percent >= 80 => ['অসাধারণ! 🎉', 'text-volt-400'],
            $percent >= 50 => ['ভালো করেছেন! 👍', 'text-emerald-400'],
            default => ['আরও চেষ্টা করুন 💪', 'text-amber-400'],
        };
    @endphp

    {{-- Score hero --}}
    <section class="relative mb-8 animate-rise overflow-hidden rounded-[2rem] glass-panel p-7 text-white sm:p-10">
        <div class="absolute inset-0 bg-grid [mask-image:linear-gradient(to_left,black,transparent)]"></div>
        <div class="absolute -top-24 -right-10 size-80 rounded-full bg-brand-600/50 blur-3xl"></div>

        <div class="relative grid items-center gap-8 md:grid-cols-[auto_1fr]">
            <div class="relative mx-auto size-44">
                <svg viewBox="0 0 36 36" class="size-44 -rotate-90">
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3" class="stroke-white/10" />
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3" stroke-linecap="round"
                        class="stroke-volt-400" stroke-dasharray="{{ $percent * 0.974 }} 100" />
                </svg>
                <div class="absolute inset-0 grid place-items-center text-center">
                    <div>
                        <p class="font-display text-5xl font-extrabold">{{ $percent }}<span class="text-2xl">%</span></p>
                        <p class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Accuracy</p>
                    </div>
                </div>
            </div>

            <div class="text-center md:text-left">
                <div class="flex flex-wrap items-center justify-center gap-2 md:justify-start">
                    <p class="font-display text-lg font-bold {{ $verdictTone }}">{{ $verdict }}</p>
                    <span class="badge {{ $quiz->difficulty->badgeClass() }}"><span class="{{ $quiz->difficulty->icon() }} size-3.5"></span> {{ $quiz->difficulty->label() }}</span>
                </div>
                <h1 class="mt-1 font-display text-2xl font-bold sm:text-3xl">{{ $quiz->title }}</h1>
                <p class="mt-1 text-sm text-ink-400">আপনার পারফরম্যান্সের বিস্তারিত নিচে দেখুন।</p>

                <div class="mt-6 grid grid-cols-3 gap-3 sm:max-w-md">
                    <div class="rounded-2xl bg-white/5 p-3 text-center ring-1 ring-white/10">
                        <p class="text-[11px] font-semibold text-ink-400 uppercase">Score</p>
                        <p class="font-display text-2xl font-bold">{{ $totalMarks }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-500/10 p-3 text-center ring-1 ring-emerald-400/20">
                        <p class="text-[11px] font-semibold text-emerald-300 uppercase">Right</p>
                        <p class="font-display text-2xl font-bold text-emerald-400">{{ $rightAnswers }}</p>
                    </div>
                    <div class="rounded-2xl bg-rose-500/10 p-3 text-center ring-1 ring-rose-400/20">
                        <p class="text-[11px] font-semibold text-rose-300 uppercase">Wrong</p>
                        <p class="font-display text-2xl font-bold text-rose-400">{{ $wrongAnswers }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-center gap-2 md:justify-start">
                    @if ($canRetake)
                        <a href="{{ route('user.quiz.show', $quiz->slug) }}" class="btn btn-volt">
                            <span class="icon-[tabler--reload] size-4"></span> Retake
                        </a>
                    @endif
                    <a href="{{ route('user.quiz.index') }}" class="btn {{ $canRetake ? 'btn-glass' : 'btn-volt' }}">
                        <span class="icon-[tabler--file-pencil] size-4"></span> All Exams
                    </a>
                    <a href="{{ route('user.quiz.leaderboard') }}" class="btn btn-glass">
                        <span class="icon-[tabler--trophy] size-4"></span> Leaderboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if ($attemptsTaken > 1)
        <div class="mb-8 flex items-center gap-2 overflow-x-auto pb-1">
            <span class="mr-1 flex shrink-0 items-center gap-1 text-xs font-bold tracking-wider text-ink-400 uppercase">
                <span class="icon-[tabler--repeat] size-4"></span> Attempts
            </span>
            @foreach (range(1, $attemptsTaken) as $number)
                <a href="{{ route('user.quiz.result', [$quiz->slug, 'attempt' => $number]) }}"
                    class="badge shrink-0 px-4 py-2 text-sm transition {{ $number === $attempt ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:text-white' }}">
                    #{{ $number }}{{ $number === $attemptsTaken ? ' · Latest' : '' }}
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="mb-4 flex items-center gap-2 font-display text-xl font-bold text-white">
        <span class="icon-[tabler--list-check] size-6 text-brand-400"></span> Answer Review
    </h2>

    <div class="grid gap-5 lg:grid-cols-2">
        @foreach ($quiz->questions as $index => $question)
            <div class="card overflow-hidden">
                <div class="flex items-start gap-3 p-5 pb-4">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl font-display text-sm font-bold {{ $question->is_correct ? 'bg-emerald-500 text-snow' : 'bg-rose-500 text-snow' }}">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3 class="flex-1 pt-1 font-semibold text-white">{{ $question->question }}</h3>
                    @if ($question->is_correct)
                        <span class="badge bg-emerald-500/10 text-emerald-300"><span class="icon-[tabler--check] size-3.5"></span> Correct</span>
                    @else
                        <span class="badge bg-rose-500/10 text-rose-300"><span class="icon-[tabler--x] size-3.5"></span> Incorrect</span>
                    @endif
                </div>
                <div class="space-y-2 px-5 pb-5">
                    @foreach ($question->answers as $answer)
                        @php
                            $isCorrect = $answer->is_correct;
                            $isUserAnswer = $question->user_answer_id == $answer->id;
                        @endphp
                        <div @class([
                            'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm ring-1',
                            'bg-emerald-500/10 text-emerald-300 ring-emerald-500/25 font-semibold' => $isCorrect,
                            'bg-rose-500/10 text-rose-300 ring-rose-500/25 font-semibold' => $isUserAnswer && !$isCorrect,
                            'bg-white/5 text-ink-300 ring-white/10' => !$isCorrect && !$isUserAnswer,
                        ])>
                            @if ($isCorrect)
                                <span class="icon-[tabler--circle-check-filled] size-5 shrink-0 text-emerald-500"></span>
                            @elseif ($isUserAnswer)
                                <span class="icon-[tabler--circle-x-filled] size-5 shrink-0 text-rose-500"></span>
                            @else
                                <span class="icon-[tabler--circle] size-5 shrink-0 text-ink-300"></span>
                            @endif
                            <span class="flex-1">{{ $answer->answer }}</span>
                            @if ($isUserAnswer)
                                <span class="text-[10px] font-bold tracking-wider uppercase opacity-70">Your answer</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
