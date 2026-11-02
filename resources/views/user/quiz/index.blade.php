@extends('layouts.app')
@use('App\Enums\Difficulty')

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

    {{-- Difficulty tiles --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        <a href="{{ route('user.quiz.index', request()->only('category')) }}"
            class="flex items-center gap-3 rounded-2xl p-3.5 transition hover:-translate-y-0.5 {{ !$difficulty ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30 ring-1 ring-brand-500' : 'card' }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ !$difficulty ? 'bg-white/20' : 'bg-white/10' }}">
                <span class="icon-[tabler--layout-grid] size-5"></span>
            </span>
            <span class="leading-tight">
                <span class="block font-bold">All Levels</span>
                <span class="text-xs opacity-75">{{ $levelCounts->sum() }} exams</span>
            </span>
        </a>
        @foreach (Difficulty::cases() as $level)
            @php $active = $difficulty === $level; @endphp
            <a href="{{ route('user.quiz.index', ['difficulty' => $level->value] + request()->only('category')) }}"
                class="flex items-center gap-3 rounded-2xl p-3.5 transition hover:-translate-y-0.5 {{ $active ? $level->solidClass() : 'card' }}">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $active ? 'bg-white/20' : $level->badgeClass() }}">
                    <span class="{{ $level->icon() }} size-5"></span>
                </span>
                <span class="leading-tight">
                    <span class="block font-bold">{{ $level->label() }}</span>
                    <span class="text-xs opacity-75">{{ $level->banglaLabel() }} · {{ $levelCounts[$level->value] ?? 0 }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div x-data="viewToggle('quiz-view')">
    {{-- Category filter: main subjects, then the selected group's sub-categories --}}
    @php
        $chipClass = fn(bool $active) => $active
            ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30'
            : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20';
        $keepLevel = request()->only('difficulty');
    @endphp
    <div class="mb-3 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('user.quiz.index', $keepLevel) }}" class="badge shrink-0 px-4 py-2 text-sm transition {{ $chipClass(!$selected) }}">
            <span class="icon-[tabler--layout-grid] size-4"></span> All
        </a>
        @foreach ($mainFilters as $category)
            <a href="{{ route('user.quiz.index', ['category' => $category->slug] + $keepLevel) }}"
                class="badge shrink-0 px-4 py-2 text-sm transition {{ $chipClass($activeGroup?->id === $category->id) }}">
                {{ $category->name }}
                @if ($subFilters->isNotEmpty() && $activeGroup?->id === $category->id)
                    <span class="icon-[tabler--chevron-down] size-3.5"></span>
                @endif
            </a>
        @endforeach
        <a href="{{ route('user.interests.edit') }}"
            class="badge shrink-0 border border-dashed border-white/20 px-4 py-2 text-sm text-ink-400 transition hover:border-brand-500/50 hover:text-white">
            <span class="icon-[tabler--adjustments-horizontal] size-4"></span> আমার বিষয়
        </a>

        {{-- Grid / list view --}}
        <div class="ml-auto flex shrink-0 rounded-xl bg-white/5 p-1 ring-1 ring-white/10" role="group" aria-label="View">
            @foreach (['grid' => ['icon-[tabler--layout-grid]', 'Card view'], 'list' => ['icon-[tabler--list]', 'List view']] as $mode => [$icon, $label])
                <button type="button" @click="set('{{ $mode }}')" title="{{ $label }}" aria-label="{{ $label }}"
                    :aria-pressed="view === '{{ $mode }}'"
                    :class="view === '{{ $mode }}' ? 'bg-brand-600 text-snow shadow' : 'text-ink-400 hover:text-white'"
                    class="grid size-8 place-items-center rounded-lg transition">
                    <span class="{{ $icon }} size-4"></span>
                </button>
            @endforeach
        </div>
    </div>

    @if ($subFilters->isNotEmpty())
        <div class="mb-6 flex gap-2 overflow-x-auto rounded-2xl bg-white/[0.03] p-2 pb-2 ring-1 ring-white/10">
            <a href="{{ route('user.quiz.index', ['category' => $activeGroup->slug] + $keepLevel) }}"
                class="badge shrink-0 px-3 py-1.5 text-xs transition {{ $chipClass($selected?->id === $activeGroup->id) }}">
                সব {{ $activeGroup->name }}
            </a>
            @foreach ($subFilters as $category)
                <a href="{{ route('user.quiz.index', ['category' => $category->slug] + $keepLevel) }}"
                    class="badge shrink-0 px-3 py-1.5 text-xs transition {{ $chipClass($selected?->id === $category->id) }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @else
        <div class="mb-3"></div>
    @endif

    @if ($quizzes->isEmpty())
        <div class="card py-16 text-center">
            <span class="icon-[tabler--file-search] mx-auto mb-3 block size-12 text-ink-300"></span>
            <p class="font-semibold text-ink-200">No quizzes found.</p>
            <p class="text-sm text-ink-400">এই ফিল্টারে এখনো কোনো কুইজ নেই।</p>
        </div>
    @else
        <div id="quiz-grid" :data-view="view"
            class="group/quizzes grid gap-5 sm:grid-cols-2 lg:grid-cols-3 data-[view=list]:gap-3 data-[view=list]:sm:grid-cols-1 data-[view=list]:lg:grid-cols-1">
            @include('user.quiz._cards')
        </div>
        <script>
            document.getElementById('quiz-grid').dataset.view = localStorage.getItem('quiz-view') || 'grid';
        </script>

        {{-- More cards load as the student scrolls; the button and the page links are fallbacks --}}
        <div x-data="loadMore(@js($quizzes->nextPageUrl()), {{ $quizzes->lastItem() }}, '#quiz-grid')" class="mt-8 flex flex-col items-center gap-3">
            <p class="text-sm text-ink-400">
                মোট {{ $quizzes->total() }}টির মধ্যে <b class="text-white" x-text="shown">{{ $quizzes->lastItem() }}</b>টি দেখানো হচ্ছে
            </p>
            <div x-ref="sentinel"></div>
            <button type="button" x-show="next" x-cloak @click="load()" :disabled="loading" class="btn btn-ghost">
                <span x-show="!loading" class="icon-[tabler--chevrons-down] size-4"></span>
                <span x-show="loading" class="size-4 animate-spin rounded-full border-2 border-white/20 border-t-brand-400"></span>
                <span x-text="loading ? 'লোড হচ্ছে…' : 'আরও দেখুন'">আরও দেখুন</span>
            </button>
            <p x-show="error" x-cloak class="text-sm text-rose-400">লোড করা যায়নি। আবার চেষ্টা করুন।</p>
            <noscript>{{ $quizzes->links() }}</noscript>
        </div>
    @endif

    </div>

    {{-- Subjects the student has not chosen: locked, with a one-click add --}}
    @if ($lockedSubjects->isNotEmpty())
        <section class="mt-12">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="flex items-center gap-2 font-display text-xl font-bold text-white">
                        <span class="icon-[tabler--lock] size-5 text-ink-400"></span> অন্য বিষয়
                    </h2>
                    <p class="text-sm text-ink-400">এই বিষয়গুলোর পরীক্ষা দিতে আগে আপনার বিষয়ে যোগ করুন।</p>
                </div>
                <a href="{{ route('user.interests.edit') }}" class="text-sm font-semibold text-brand-400 hover:text-brand-300">সব বিষয় সাজান →</a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lockedSubjects as $subject)
                    <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-white/10">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/5 text-ink-500">
                            <span class="icon-[tabler--lock] size-5"></span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-ink-200">{{ $subject->name }}</p>
                            <p class="truncate text-xs text-ink-500">
                                {{ $subject->exam_count }}টি পরীক্ষা
                                @if ($subject->children->isNotEmpty())
                                    · {{ $subject->children->pluck('name')->take(3)->implode(', ') }}{{ $subject->children->count() > 3 ? '…' : '' }}
                                @endif
                            </p>
                        </div>
                        <form action="{{ route('user.interests.add', $subject) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-soft btn-sm">
                                <span class="icon-[tabler--plus] size-3.5"></span> যোগ করুন
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
