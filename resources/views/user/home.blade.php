@extends('layouts.app')

@section('title', 'Home')

@section('content')
    {{-- Hero --}}
    <section class="relative mb-6 animate-rise overflow-hidden rounded-[2rem] glass-panel p-7 text-white sm:p-10">
        <div class="absolute inset-0 bg-grid [mask-image:linear-gradient(to_left,black,transparent_70%)]"></div>
        <div class="absolute -top-28 -right-16 size-96 rounded-full bg-brand-600/50 blur-3xl"></div>
        <div class="absolute -bottom-32 left-10 size-72 rounded-full bg-volt-400/15 blur-3xl"></div>

        <div class="relative grid items-center gap-8 lg:grid-cols-[1fr_auto]">
            <div>
                <span class="badge mb-4 bg-white/10 text-volt-300 ring-1 ring-white/15">
                    <span class="icon-[tabler--sparkles] size-3.5"></span> {{ now()->format('l, d M') }}
                </span>
                <h1 class="font-display text-3xl leading-tight font-extrabold tracking-tight sm:text-5xl">
                    স্বাগতম, <span class="text-volt-400">{{ Auth::user()->name }}</span>! 👋
                </h1>
                <p class="mt-3 max-w-lg text-ink-300 sm:text-lg">আজকের কুইজ প্রতিযোগিতার জন্য আপনি কি প্রস্তুত?</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('user.quiz.index') }}" class="btn btn-volt btn-lg">
                        <span class="icon-[tabler--player-play-filled] size-5"></span> কুইজ শুরু করুন
                    </a>
                    <a href="{{ route('user.quiz.leaderboard') }}" class="btn btn-glass btn-lg">
                        <span class="icon-[tabler--trophy] size-5"></span> Leaderboard
                    </a>
                </div>
            </div>

            {{-- Rank medallion --}}
            <div class="hidden animate-float lg:block">
                <div class="relative grid size-52 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-brand-800 shadow-2xl shadow-brand-900/50 ring-8 ring-white/5">
                    <div class="absolute inset-3 rounded-full border-2 border-dashed border-white/20"></div>
                    <div class="text-center">
                        <p class="text-xs font-bold tracking-[0.25em] text-brand-200 uppercase">Your Rank</p>
                        <p class="font-display text-6xl font-extrabold">{{ $ranking ? '#' . $ranking : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    @php
        $stats = [
            ['মোট স্কোর', $totalMarks, 'Points earned', 'icon-[tabler--star-filled]', 'bg-amber-400 text-amber-950'],
            ['সম্পন্ন কুইজ', $quiz_attempted . ' টি', 'Exams completed', 'icon-[tabler--checklist]', 'bg-volt-400 text-snow'],
            ['র‍্যাঙ্কিং', $ranking ? '#' . $ranking : '—', 'By accuracy', 'icon-[tabler--trophy-filled]', 'bg-brand-500 text-snow'],
        ];
    @endphp
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($stats as $i => [$label, $value, $hint, $icon, $tone])
            <div class="card group animate-rise p-6 transition hover:-translate-y-1" style="animation-delay: {{ ($i + 1) * 90 }}ms">
                <div class="flex items-center gap-4">
                    <span class="grid size-14 shrink-0 place-items-center rounded-2xl {{ $tone }} shadow-lg transition group-hover:rotate-6">
                        <span class="{{ $icon }} size-7"></span>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-ink-400">{{ $label }}</p>
                        <p class="font-display text-3xl font-extrabold tracking-tight text-white">{{ $value }}</p>
                        <p class="text-xs text-ink-400">{{ $hint }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Shortcuts --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('user.my.quiz') }}"
            class="card group flex items-center gap-4 p-6 transition hover:-translate-y-1 hover:ring-brand-500/25">
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
                <span class="icon-[tabler--history] size-6"></span>
            </span>
            <div class="flex-1">
                <p class="font-display text-lg font-bold text-white">My Exams</p>
                <p class="text-sm text-ink-400">আগের পরীক্ষাগুলোর ফলাফল দেখুন</p>
            </div>
            <span class="icon-[tabler--arrow-up-right] size-6 text-ink-300 transition group-hover:text-brand-400"></span>
        </a>
        <a href="{{ route('user.profile') }}"
            class="card group flex items-center gap-4 p-6 transition hover:-translate-y-1 hover:ring-brand-500/25">
            <span class="grid size-12 place-items-center rounded-2xl bg-emerald-500/10 text-emerald-400">
                <span class="icon-[tabler--user-circle] size-6"></span>
            </span>
            <div class="flex-1">
                <p class="font-display text-lg font-bold text-white">Profile</p>
                <p class="text-sm text-ink-400">আপনার তথ্য ও পাসওয়ার্ড পরিবর্তন করুন</p>
            </div>
            <span class="icon-[tabler--arrow-up-right] size-6 text-ink-300 transition group-hover:text-brand-400"></span>
        </a>
    </div>
@endsection
