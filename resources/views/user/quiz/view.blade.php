@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
    @php
        $questionCount = $quiz->questions->count();
        $totalSeconds = $examSeconds;
    @endphp

    {{-- Start screen --}}
    <div id="start-screen" class="grid min-h-[65vh] place-items-center">
        <div class="relative w-full max-w-lg animate-rise overflow-hidden rounded-[2rem] glass-panel p-8 text-center text-white sm:p-10">
            <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
            <div class="absolute -top-24 left-1/2 size-72 -translate-x-1/2 rounded-full bg-brand-600/50 blur-3xl"></div>

            <div class="relative">
                <span class="mx-auto mb-6 grid size-20 animate-float place-items-center rounded-3xl bg-volt-400 text-snow shadow-xl shadow-volt-400/20">
                    <span class="icon-[tabler--alarm] size-10"></span>
                </span>
                <div class="mb-3 flex flex-wrap justify-center gap-2">
                    <span class="badge bg-white/10 text-volt-300 ring-1 ring-white/15">{{ $quiz->category?->name }}</span>
                    <span class="badge {{ $quiz->difficulty->badgeClass() }}"><span class="{{ $quiz->difficulty->icon() }} size-3.5"></span> {{ $quiz->difficulty->label() }}</span>
                    @if ($maxAttempts > 1)
                        <span class="badge bg-white/10 text-ink-200 ring-1 ring-white/15"><span class="icon-[tabler--repeat] size-3.5"></span> Attempt {{ $attempt }}/{{ $maxAttempts }}</span>
                    @endif
                </div>
                <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ $quiz->title }}</h1>
                @if ($quiz->description)
                    <p class="mt-2 text-sm text-ink-300">{{ $quiz->description }}</p>
                @endif

                <div class="my-8 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <span class="icon-[tabler--list-numbers] size-5 text-volt-400"></span>
                        <p class="mt-1 font-display text-2xl font-bold">{{ $questionCount }}</p>
                        <p class="text-xs text-ink-400">প্রশ্ন</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <span class="icon-[tabler--hourglass] size-5 text-volt-400"></span>
                        <p class="mt-1 font-display text-2xl font-bold">{{ gmdate($totalSeconds >= 3600 ? 'H:i:s' : 'i:s', $totalSeconds) }}</p>
                        <p class="text-xs text-ink-400">মোট সময়</p>
                    </div>
                </div>

                <ul class="mb-8 space-y-2 text-left text-sm text-ink-300">
                    <li class="flex gap-2">
                        <span class="icon-[tabler--circle-check] mt-0.5 size-4 shrink-0 text-volt-400"></span>
                        @if ($quiz->duration_minutes)
                            পুরো পরীক্ষাটি {{ $quiz->duration_minutes }} মিনিটের মধ্যে শেষ করতে হবে।
                        @else
                            প্রতিটি প্রশ্নের জন্য {{ $secondsPerQuestion }} সেকেন্ড সময়।
                        @endif
                    </li>
                    <li class="flex gap-2"><span class="icon-[tabler--circle-check] mt-0.5 size-4 shrink-0 text-volt-400"></span> সময় শেষ হলে উত্তর অটোমেটিক জমা হবে।</li>
                    <li class="flex gap-2"><span class="icon-[tabler--circle-check] mt-0.5 size-4 shrink-0 text-volt-400"></span> একবার জমা দিলে আর পরিবর্তন করা যাবে না।</li>
                </ul>

                <button id="startBtn" type="button" class="btn btn-volt btn-lg w-full">
                    <span class="icon-[tabler--player-play-filled] size-5"></span> কুইজ শুরু করুন
                </button>
                <a href="{{ route('user.quiz.index') }}" class="mt-3 inline-block text-sm text-ink-400 hover:text-white">ফিরে যান</a>
            </div>
        </div>
    </div>

    {{-- Quiz --}}
    <div id="quiz-content" class="hidden">
        {{-- HUD --}}
        <div class="sticky top-[5.25rem] z-30 mb-6">
            <div class="overflow-hidden rounded-2xl glass-panel text-white">
                <div class="flex items-center gap-3 p-3 sm:gap-4 sm:p-4">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-display font-bold sm:text-lg">{{ $quiz->title }}</p>
                        <p class="hidden text-xs text-ink-400 sm:block">সময় শেষ হওয়ার আগে উত্তর জমা দিন।</p>
                    </div>
                    <div class="rounded-xl bg-white/10 px-3 py-1.5 text-center">
                        <p class="text-[9px] font-bold tracking-wider text-ink-400 uppercase">Answered</p>
                        <p class="font-display font-bold"><span id="answered-count" class="text-volt-400">0</span><span class="text-ink-400">/{{ $questionCount }}</span></p>
                    </div>
                    <div id="timer-box" class="rounded-xl bg-volt-400 px-3 py-1.5 text-center text-snow transition-colors">
                        <p class="text-[9px] font-bold tracking-wider uppercase opacity-70">Time Left</p>
                        <p id="timer" class="font-display font-extrabold tabular-nums">00:00</p>
                    </div>
                    <a href="{{ route('home') }}" onclick="return confirm('আপনি কি নিশ্চিত?')"
                        class="grid size-10 shrink-0 place-items-center rounded-xl text-ink-400 transition hover:bg-rose-500/15 hover:text-rose-400" title="Cancel">
                        <span class="icon-[tabler--x] size-5"></span>
                    </a>
                </div>
                <div class="h-1 bg-white/10">
                    <div id="progress-bar" class="h-full w-0 bg-gradient-to-r from-volt-400 to-emerald-400 transition-all duration-500"></div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_15rem]">
            <form action="{{ route('user.quiz.submit') }}" method="POST" id="quizForm" class="space-y-5">
                @csrf
                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

                @foreach ($quiz->questions as $key => $question)
                    <div id="q-{{ $question->id }}" class="card scroll-mt-44 p-5 sm:p-6">
                        <input type="hidden" name="question_id[]" value="{{ $question->id }}">
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
                        @foreach ($quiz->questions as $key => $question)
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalQuestions = {{ $questionCount }};
            const totalTimeInSeconds = {{ $totalSeconds }};
            const storageKey = 'quiz_timer_{{ $quiz->id }}_{{ $attempt }}';

            const startBtn = document.getElementById('startBtn');
            const startScreen = document.getElementById('start-screen');
            const quizContent = document.getElementById('quiz-content');
            const timerDisplay = document.getElementById('timer');
            const timerBox = document.getElementById('timer-box');
            const quizForm = document.getElementById('quizForm');
            const answeredDisplay = document.getElementById('answered-count');
            const progressBar = document.getElementById('progress-bar');
            const radios = quizForm.querySelectorAll('input[type="radio"]');

            let timeRemaining;
            let timerInterval;
            let submitting = false;

            const savedTime = localStorage.getItem(storageKey);
            if (savedTime) {
                initQuiz(parseInt(savedTime));
            }

            startBtn.addEventListener('click', () => initQuiz(totalTimeInSeconds));

            function initQuiz(startTime) {
                timeRemaining = startTime;
                startScreen.classList.add('hidden');
                quizContent.classList.remove('hidden');
                timerInterval = setInterval(updateTimer, 1000);
                updateTimer();
            }

            function updateTimer() {
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    handleTimeUp();
                } else {
                    timeRemaining--;
                    localStorage.setItem(storageKey, timeRemaining);
                }

                // ১ মিনিটের কম সময় থাকলে টাইমার লাল হয়ে যাবে
                if (timeRemaining < 60) {
                    timerBox.classList.remove('bg-volt-400', 'text-snow');
                    timerBox.classList.add('bg-white', 'text-volt-500', 'animate-pulse');
                }
            }

            function handleTimeUp() {
                localStorage.removeItem(storageKey);
                submitting = true;
                alert('সময় শেষ! আপনার উত্তরগুলো অটোমেটিক সাবমিট হচ্ছে।');
                quizForm.submit();
            }

            quizForm.addEventListener('submit', () => {
                submitting = true;
                localStorage.removeItem(storageKey);
            });

            window.addEventListener('beforeunload', (e) => {
                if (!submitting && timeRemaining > 0 && !quizContent.classList.contains('hidden')) {
                    e.preventDefault();
                    e.returnValue = 'আপনি কি কুইজ ছেড়ে চলে যেতে চান?';
                }
            });

            function updateAnsweredCount() {
                const answered = new Set();
                radios.forEach(radio => radio.checked && answered.add(radio.dataset.question));

                answeredDisplay.textContent = answered.size;
                progressBar.style.width = `${(answered.size / (totalQuestions || 1)) * 100}%`;

                document.querySelectorAll('[data-nav]').forEach(link => {
                    const done = answered.has(link.dataset.nav);
                    ['bg-brand-600', 'text-snow', 'ring-brand-600'].forEach(c => link.classList.toggle(c, done));
                    ['bg-white/5', 'text-ink-400', 'ring-white/10'].forEach(c => link.classList.toggle(c, !done));
                });
            }

            radios.forEach(radio => radio.addEventListener('change', updateAnsweredCount));
            updateAnsweredCount();
        });
    </script>
@endpush
