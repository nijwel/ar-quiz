@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
    @php
        $formatTime = fn(int $seconds) => gmdate($seconds >= 3600 ? 'H:i:s' : 'i:s', $seconds);
        $examRules = [
            ['icon-[tabler--browser-x]', 'অন্য ট্যাবে গেলে বা ব্রাউজার মিনিমাইজ করলে'],
            ['icon-[tabler--app-window]', 'অন্য উইন্ডো, অ্যাপ বা ব্রাউজারের অ্যাড্রেস বারে ক্লিক করলে'],
            ['icon-[tabler--refresh-alert]', 'পেজ রিলোড, বন্ধ বা Back করলে'],
            ['icon-[tabler--arrows-minimize]', 'ফুলস্ক্রিন থেকে বের হলে (Esc চাপলে)'],
        ];
    @endphp

    <div id="exam"
        data-start-url="{{ route('user.quiz.start', $quiz->slug) }}"
        data-submit-url="{{ route('user.quiz.submit') }}"
        data-csrf="{{ csrf_token() }}"
        data-total-questions="{{ $questionCount }}"
        data-resume-seconds="{{ $resumeAttempt?->remainingSeconds() }}">

        @if ($resumeAttempt)
            {{-- Resume screen: the attempt is already running on the server --}}
            <div data-screen="resume" class="grid min-h-[65vh] place-items-center">
                <div class="relative w-full max-w-lg animate-rise overflow-hidden rounded-[2rem] glass-panel p-8 text-center text-white sm:p-10">
                    <span class="mx-auto mb-6 grid size-20 place-items-center rounded-3xl bg-amber-500 text-snow shadow-xl shadow-amber-500/20">
                        <span class="icon-[tabler--player-pause-filled] size-10"></span>
                    </span>
                    <h1 class="font-display text-2xl font-extrabold">{{ $quiz->title }}</h1>
                    <p class="mt-2 text-sm text-ink-300">আপনার পরীক্ষা চলমান আছে। টাইমার থেমে নেই।</p>
                    <p class="my-6 font-display text-4xl font-extrabold text-volt-400">{{ $formatTime($resumeAttempt->remainingSeconds()) }}</p>
                    <button data-action="resume" type="button" class="btn btn-volt btn-lg w-full">
                        <span class="icon-[tabler--player-play-filled] size-5"></span> পরীক্ষা চালিয়ে যান
                    </button>
                    <p class="mt-4 text-xs text-ink-400">আবার ট্যাব বা উইন্ডো পরিবর্তন করলে পরীক্ষা জমা হয়ে যাবে।</p>
                </div>
            </div>
        @else
            {{-- Start screen --}}
            <div data-screen="start" class="grid min-h-[65vh] place-items-center">
                <div class="relative w-full max-w-xl animate-rise overflow-hidden rounded-[2rem] glass-panel p-6 text-white sm:p-10">
                    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
                    <div class="absolute -top-24 left-1/2 size-72 -translate-x-1/2 rounded-full bg-brand-600/50 blur-3xl"></div>

                    <div class="relative">
                        <div class="text-center">
                            <div class="mb-3 flex flex-wrap justify-center gap-2">
                                <span class="badge bg-white/10 text-volt-300 ring-1 ring-white/15">{{ $quiz->category?->name }}</span>
                                <span class="badge {{ $quiz->difficulty->badgeClass() }}"><span class="{{ $quiz->difficulty->icon() }} size-3.5"></span> {{ $quiz->difficulty->label() }}</span>
                                @if ($maxAttempts > 1)
                                    <span class="badge bg-white/10 text-ink-200 ring-1 ring-white/15"><span class="icon-[tabler--repeat] size-3.5"></span> Attempt {{ $attemptNumber }}/{{ $maxAttempts }}</span>
                                @endif
                            </div>
                            <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ $quiz->title }}</h1>
                            @if ($quiz->description)
                                <p class="mt-2 text-sm text-ink-300">{{ $quiz->description }}</p>
                            @endif
                        </div>

                        <div class="my-6 grid grid-cols-2 gap-3 text-center">
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <span class="icon-[tabler--list-numbers] size-5 text-volt-400"></span>
                                <p class="mt-1 font-display text-2xl font-bold">{{ $questionCount }}</p>
                                <p class="text-xs text-ink-400">প্রশ্ন</p>
                            </div>
                            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                                <span class="icon-[tabler--hourglass] size-5 text-volt-400"></span>
                                <p class="mt-1 font-display text-2xl font-bold">{{ $formatTime($examSeconds) }}</p>
                                <p class="text-xs text-ink-400">
                                    {{ $quiz->duration_minutes ? 'মোট সময়' : "প্রতি প্রশ্নে {$secondsPerQuestion} সেকেন্ড" }}
                                </p>
                            </div>
                        </div>

                        {{-- Anti-cheat rules --}}
                        <div class="rounded-2xl bg-rose-500/10 p-4 ring-1 ring-rose-500/30 sm:p-5">
                            <p class="mb-3 flex items-center gap-2 font-display font-bold text-rose-300">
                                <span class="icon-[tabler--shield-lock] size-5"></span> পরীক্ষার নিয়ম — সাবধানে পড়ুন
                            </p>
                            <p class="mb-3 text-sm text-ink-200">
                                পরীক্ষা শুরু হলে নিচের যেকোনো কাজ করলে আপনার পরীক্ষা <b class="text-rose-300">সাথে সাথে স্বয়ংক্রিয়ভাবে জমা</b> হয়ে যাবে,
                                এবং তখন পর্যন্ত দেওয়া উত্তরগুলোই গণনা হবে:
                            </p>
                            <ul class="space-y-2 text-sm text-ink-200">
                                @foreach ($examRules as [$icon, $rule])
                                    <li class="flex gap-2"><span class="{{ $icon }} mt-0.5 size-4 shrink-0 text-rose-400"></span> {{ $rule }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-3 border-t border-white/10 pt-3 text-xs text-ink-400">
                                পরীক্ষা ফুলস্ক্রিনে চলবে। কপি, পেস্ট, রাইট-ক্লিক ও কিবোর্ড শর্টকাট বন্ধ থাকবে। ফোনে কল বা নোটিফিকেশনে গেলেও পরীক্ষা জমা হয়ে যেতে পারে।
                            </p>
                        </div>

                        <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl bg-white/5 p-4 text-sm ring-1 ring-white/10 transition has-checked:ring-volt-400">
                            <input data-field="agree" type="checkbox" class="mt-0.5 size-4 shrink-0 accent-brand-600">
                            <span class="text-ink-200">আমি নিয়মগুলো পড়েছি এবং বুঝেছি। পরীক্ষা চলাকালীন আমি এই পেজ ছেড়ে যাব না।</span>
                        </label>

                        <p data-field="start-error" class="mt-3 hidden text-center text-sm font-medium text-rose-400"></p>

                        <button data-action="start" type="button" class="btn btn-volt btn-lg mt-5 w-full" disabled>
                            <span class="icon-[tabler--player-play-filled] size-5"></span> পরীক্ষা শুরু করুন
                        </button>
                        <a href="{{ route('user.quiz.index') }}" class="mt-3 block text-center text-sm text-ink-400 hover:text-white">ফিরে যান</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Running exam --}}
        <div data-screen="exam" class="hidden">
            <div class="sticky top-3 z-30 mb-6">
                <div class="overflow-hidden rounded-2xl glass-panel text-white">
                    <div class="flex items-center gap-3 p-3 sm:gap-4 sm:p-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-display font-bold sm:text-lg">{{ $quiz->title }}</p>
                            <p class="hidden items-center gap-1 text-xs text-rose-300 sm:flex">
                                <span class="icon-[tabler--shield-lock] size-3.5"></span> পেজ ছেড়ে গেলে পরীক্ষা জমা হয়ে যাবে
                            </p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-3 py-1.5 text-center">
                            <p class="text-[9px] font-bold tracking-wider text-ink-400 uppercase">Answered</p>
                            <p class="font-display font-bold"><span data-field="answered" class="text-volt-400">0</span><span class="text-ink-400">/{{ $questionCount }}</span></p>
                        </div>
                        <div data-field="timer-box" class="rounded-xl bg-volt-400 px-3 py-1.5 text-center text-snow transition-colors">
                            <p class="text-[9px] font-bold tracking-wider uppercase opacity-70">Time Left</p>
                            <p data-field="timer" class="font-display font-extrabold tabular-nums">00:00</p>
                        </div>
                    </div>
                    <div class="h-1 bg-white/10">
                        <div data-field="progress" class="h-full w-0 bg-gradient-to-r from-volt-400 to-emerald-400 transition-all duration-500"></div>
                    </div>
                </div>
            </div>

            <div data-field="questions" class="select-none">{!! $questionsHtml !!}</div>
        </div>

        {{-- Confirm manual submit (an in-page dialog; native confirm() would blur the window) --}}
        <div data-screen="confirm" class="fixed inset-0 z-50 hidden place-items-center bg-black/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-3xl bg-ink-950/90 p-6 text-center shadow-2xl ring-1 ring-white/10 backdrop-blur-2xl">
                <span class="icon-[tabler--send] mx-auto mb-3 block size-10 text-volt-400"></span>
                <p class="font-display text-lg font-bold text-white">উত্তর জমা দিবেন?</p>
                <p data-field="confirm-text" class="mt-1 text-sm text-ink-400"></p>
                <div class="mt-6 grid grid-cols-2 gap-2">
                    <button data-action="cancel-submit" type="button" class="btn btn-ghost">ফিরে যান</button>
                    <button data-action="confirm-submit" type="button" class="btn btn-primary">জমা দিন</button>
                </div>
            </div>
        </div>

        {{-- Submitting overlay --}}
        <div data-screen="submitting" class="fixed inset-0 z-50 hidden place-items-center bg-black/80 p-4 backdrop-blur-md">
            <div class="max-w-sm text-center">
                <span class="mx-auto mb-4 block size-12 animate-spin rounded-full border-4 border-white/20 border-t-volt-400"></span>
                <p data-field="submitting-title" class="font-display text-xl font-bold text-white">উত্তর জমা হচ্ছে…</p>
                <p data-field="submitting-text" class="mt-2 text-sm text-ink-300"></p>
            </div>
        </div>
    </div>
@endsection
