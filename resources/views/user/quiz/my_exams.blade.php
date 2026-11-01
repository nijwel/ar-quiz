@extends('layouts.app')

@section('title', 'My Exams')

@section('content')
    <x-page-hero icon="icon-[tabler--checklist]" title="My Exams" subtitle="আপনার দেওয়া সকল পরীক্ষা ও ফলাফল।">
        <x-slot:actions>
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-right ring-1 ring-white/15">
                <p class="text-[11px] font-bold tracking-wider text-ink-400 uppercase">Total Points</p>
                <p class="flex items-center justify-end gap-1.5 font-display text-2xl font-bold">
                    <span class="icon-[tabler--star-filled] size-5 text-volt-400"></span> {{ $totalMarks }}
                </p>
            </div>
        </x-slot:actions>
    </x-page-hero>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($quizzes as $quiz)
            @php
                $correct = $quiz->userAnswers->where('status', 'correct')->count();
                $result = [
                    'attempted' => true,
                    'right' => $correct,
                    'wrong' => $quiz->userAnswers->count() - $correct,
                    'total_questions' => $quiz->questions_count,
                    'marks' => $correct,
                ];
            @endphp
            <x-quiz-card :quiz="$quiz" :result="$result" :delay="min($loop->index, 8) * 60" />
        @empty
            <div class="card col-span-full py-16 text-center">
                <span class="icon-[tabler--clipboard-off] mx-auto mb-3 block size-12 text-ink-300"></span>
                <p class="font-semibold text-ink-200">এখনো কোনো পরীক্ষা দেননি।</p>
                <a href="{{ route('user.quiz.index') }}" class="btn btn-primary mt-4">
                    <span class="icon-[tabler--player-play-filled] size-4"></span> প্রথম কুইজ শুরু করুন
                </a>
            </div>
        @endforelse
    </div>
@endsection
