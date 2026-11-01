@extends('layouts.app')

@section('title', 'Exams')

@section('content')
    <x-page-hero icon="icon-[tabler--file-pencil]" title="Available Quizzes"
        subtitle="আপনার পছন্দের কুইজটি বেছে নিন এবং নিজেকে যাচাই করুন।">
        <x-slot:actions>
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-right ring-1 ring-white/15">
                <p class="text-[11px] font-bold tracking-wider text-ink-400 uppercase">Total Points</p>
                <p class="flex items-center justify-end gap-1.5 font-display text-2xl font-bold">
                    <span class="icon-[tabler--star-filled] size-5 text-volt-400"></span> {{ $totalMarks }}
                </p>
            </div>
        </x-slot:actions>
    </x-page-hero>

    {{-- Category chips --}}
    @php $current = request('category'); @endphp
    <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('user.quiz.index') }}"
            class="badge shrink-0 px-4 py-2 text-sm transition {{ !$current ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">
            <span class="icon-[tabler--layout-grid] size-4"></span> All
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('user.quiz.index', ['category' => $category->slug]) }}"
                class="badge shrink-0 px-4 py-2 text-sm transition {{ $current == $category->slug ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($quizzes as $quiz)
            <x-quiz-card :quiz="$quiz" :result="$quizResults[$quiz->id]" :delay="min($loop->index, 8) * 60" />
        @empty
            <div class="card col-span-full py-16 text-center">
                <span class="icon-[tabler--file-search] mx-auto mb-3 block size-12 text-ink-300"></span>
                <p class="font-semibold text-ink-200">No quizzes found.</p>
                <p class="text-sm text-ink-400">এই ক্যাটেগরিতে এখনো কোনো কুইজ নেই।</p>
            </div>
        @endforelse
    </div>
@endsection
