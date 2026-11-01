@extends('layouts.app')

@section('title', $participant->name)

@section('content')
    <x-page-hero eyebrow="Participant" title="{{ $participant->name }}" subtitle="{{ $participant->email }}">
        <x-slot:actions>
            <a href="{{ route('participant.index') }}" class="btn btn-glass">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back
            </a>
        </x-slot:actions>
    </x-page-hero>

    <h2 class="mb-4 flex items-center gap-2 font-display text-xl font-bold text-white">
        <span class="icon-[tabler--report-analytics] size-6 text-brand-400"></span> Exam History & Performance
    </h2>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($quizzes as $quiz)
            @php
                $latest = $latestAttempts[$quiz->id];
                $reason = \App\Enums\SubmitReason::tryFrom((string) $latest->submit_reason);
                $totalQ = $quiz->questions_count;
                $attempted = (int) $latest->answered;
                $right = (int) $latest->correct;
                $wrong = $attempted - $right;
                $percentage = $totalQ > 0 ? round(($right / $totalQ) * 100) : 0;
                $ring = $percentage >= 70 ? 'text-emerald-500' : ($percentage >= 40 ? 'text-amber-500' : 'text-rose-500');
            @endphp
            <div class="card animate-rise p-6 transition hover:-translate-y-1">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-white">{{ $quiz->title }}</h3>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="badge bg-white/10 text-ink-300">Attempt {{ $latest->attempt }}</span>
                            @if ($reason)
                                <span class="badge {{ $reason->badgeClass() }}" title="{{ $reason->banglaLabel() }}">
                                    @if ($reason->isViolation())
                                        <span class="icon-[tabler--alert-triangle] size-3.5"></span>
                                    @endif
                                    {{ $reason->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- Score ring --}}
                    <div class="relative size-16 shrink-0">
                        <svg viewBox="0 0 36 36" class="size-16 -rotate-90">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3.5" class="stroke-white/10" />
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3.5" stroke-linecap="round"
                                stroke="currentColor" class="{{ $ring }}"
                                stroke-dasharray="{{ $percentage * 0.974 }} 100" />
                        </svg>
                        <span class="absolute inset-0 grid place-items-center font-display text-sm font-bold text-white">{{ $percentage }}%</span>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-xl bg-white/5 p-3">
                        <dt class="text-xs text-ink-400">Questions</dt>
                        <dd class="font-display text-lg font-bold text-white">{{ $totalQ }}</dd>
                    </div>
                    <div class="rounded-xl bg-brand-500/10 p-3">
                        <dt class="text-xs text-brand-400">Attempted</dt>
                        <dd class="font-display text-lg font-bold text-brand-300">{{ $attempted }}</dd>
                    </div>
                    <div class="rounded-xl bg-emerald-500/10 p-3">
                        <dt class="text-xs text-emerald-400">Correct</dt>
                        <dd class="font-display text-lg font-bold text-emerald-300">{{ $right }}</dd>
                    </div>
                    <div class="rounded-xl bg-rose-500/10 p-3">
                        <dt class="text-xs text-rose-400">Wrong</dt>
                        <dd class="font-display text-lg font-bold text-rose-300">{{ $wrong }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-baseline justify-between border-t border-white/10 pt-4">
                    <span class="text-xs font-bold tracking-wider text-ink-400 uppercase">Score</span>
                    <span><b class="font-display text-2xl text-white">{{ $right }}</b><span class="text-ink-400"> / {{ $totalQ }}</span></span>
                </div>
            </div>
        @empty
            <div class="card col-span-full py-16 text-center">
                <span class="icon-[tabler--mood-empty] mx-auto mb-2 block size-10 text-ink-300"></span>
                <p class="text-ink-400">এই পরীক্ষার্থী এখনো কোনো পরীক্ষা দেয়নি।</p>
            </div>
        @endforelse
    </div>
@endsection
