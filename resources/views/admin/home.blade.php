@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-hero eyebrow="{{ now()->format('l, d M Y') }}" title="Welcome back, {{ Auth::user()->name }} 👋"
        subtitle="আজকের কুইজ স্ট্যাটাস এবং পারফরম্যান্স রিপোর্ট চেক করুন।">
        <x-slot:actions>
            <a href="{{ route('quiz.create') }}" class="btn btn-volt">
                <span class="icon-[tabler--plus] size-4"></span> Create Quiz
            </a>
            <a href="{{ route('participant.index') }}" class="btn btn-glass">
                <span class="icon-[tabler--users-group] size-4"></span> Students
            </a>
        </x-slot:actions>
    </x-page-hero>

    @php
        $stats = [
            ['Total Quizzes', $quizCount, 'icon-[tabler--file-text]', 'bg-brand-500/10 text-brand-400', 'from-brand-500/10'],
            ['Participants', $userCount, 'icon-[tabler--users-group]', 'bg-emerald-500/10 text-emerald-400', 'from-emerald-500/10'],
            ['Live Right Now', $activeCount, 'icon-[tabler--broadcast]', 'bg-amber-500/10 text-amber-400', 'from-amber-500/10'],
        ];
    @endphp

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        @foreach ($stats as $i => [$label, $value, $icon, $tone, $glow])
            <div class="card group relative animate-rise overflow-hidden p-6 transition hover:-translate-y-1"
                style="animation-delay: {{ $i * 80 }}ms">
                <div class="absolute inset-0 bg-gradient-to-br {{ $glow }} to-transparent opacity-0 transition group-hover:opacity-100"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-ink-400">{{ $label }}</p>
                        <p class="mt-2 font-display text-4xl font-extrabold tracking-tight text-white">{{ number_format($value) }}</p>
                    </div>
                    <span class="grid size-12 place-items-center rounded-2xl {{ $tone }}">
                        <span class="{{ $icon }} size-6"></span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Recent quizzes --}}
        <div class="card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between px-6 pt-6 pb-4">
                <div>
                    <h2 class="font-display text-lg font-bold text-white">Recent Quizzes</h2>
                    <p class="text-sm text-ink-400">সর্বশেষ তৈরি করা কুইজগুলো</p>
                </div>
                <a href="{{ route('quiz.index') }}" class="btn btn-soft btn-sm">
                    View all <span class="icon-[tabler--arrow-right] size-3.5"></span>
                </a>
            </div>
            <ul class="divide-y divide-white/5">
                @forelse ($recentQuizzes as $quiz)
                    @php
                        $live = $quiz->start_exam_at && $quiz->end_exam_at && now()->between($quiz->start_exam_at, $quiz->end_exam_at);
                    @endphp
                    <li class="flex items-center gap-4 px-6 py-4 transition hover:bg-white/5">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-white/10 font-display text-sm font-bold text-volt-400">
                            {{ str_pad($quiz->questions_count, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-white">{{ $quiz->title }}</p>
                            <p class="text-xs text-ink-400">{{ $quiz->category?->name ?? 'Uncategorized' }} · {{ $quiz->questions_count }} questions</p>
                        </div>
                        @if ($live)
                            <span class="badge bg-amber-500/10 text-amber-300"><span class="size-1.5 animate-pulse rounded-full bg-amber-500"></span> Live</span>
                        @endif
                        <a href="{{ route('quiz.edit', $quiz->id) }}" class="btn btn-ghost btn-icon" title="Edit">
                            <span class="icon-[tabler--pencil] size-4"></span>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-ink-400">এখনো কোনো কুইজ তৈরি হয়নি।</li>
                @endforelse
            </ul>
        </div>

        {{-- Quick actions --}}
        <div class="card p-6">
            <h2 class="mb-4 font-display text-lg font-bold text-white">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach ([
                    ['Create Quiz', route('quiz.create'), 'icon-[tabler--square-rounded-plus]', 'text-brand-400 bg-brand-500/10'],
                    ['Manage Quizzes', route('quiz.index'), 'icon-[tabler--list-details]', 'text-sky-400 bg-sky-500/10'],
                    ['Categories', route('category.index'), 'icon-[tabler--category]', 'text-fuchsia-400 bg-fuchsia-500/10'],
                    ['Students', route('participant.index'), 'icon-[tabler--school]', 'text-emerald-400 bg-emerald-500/10'],
                ] as [$label, $href, $icon, $tone])
                    <a href="{{ $href }}"
                        class="group flex flex-col items-start gap-3 rounded-2xl p-4 ring-1 ring-white/10 transition hover:-translate-y-0.5 hover:ring-brand-500/25 hover:shadow-lg hover:shadow-brand-500/5">
                        <span class="grid size-10 place-items-center rounded-xl {{ $tone }}">
                            <span class="{{ $icon }} size-5"></span>
                        </span>
                        <span class="text-sm font-semibold text-white">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
