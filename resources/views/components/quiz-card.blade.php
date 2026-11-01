@props(['quiz', 'result', 'delay' => 0])

@php
    $start = $quiz->start_exam_at ? \Carbon\Carbon::parse($quiz->start_exam_at) : null;
    $end = $quiz->end_exam_at ? \Carbon\Carbon::parse($quiz->end_exam_at) : null;

    if ($result['attempted']) {
        $status = ['Completed', 'bg-emerald-500/10 text-emerald-300', 'icon-[tabler--circle-check-filled]'];
    } elseif ($start && $start->isFuture()) {
        $status = ['Upcoming', 'bg-sky-500/10 text-sky-300', 'icon-[tabler--calendar-time]'];
    } elseif ($end && $end->isPast()) {
        $status = ['Expired', 'bg-rose-500/10 text-rose-300', 'icon-[tabler--clock-x]'];
    } elseif ($start && $end) {
        $status = ['Live Now', 'bg-amber-500/10 text-amber-300', 'icon-[tabler--broadcast]'];
    } else {
        $status = ['New', 'bg-brand-500/10 text-brand-300', 'icon-[tabler--sparkles]'];
    }

    $percent = $result['total_questions'] ? round(($result['right'] / $result['total_questions']) * 100) : 0;
@endphp

<article class="card group flex animate-rise flex-col p-5 transition duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-brand-900/5"
    style="animation-delay: {{ $delay }}ms">
    <div class="mb-4 flex items-center justify-between gap-2">
        <span class="badge bg-white/10 text-ink-300">
            <span class="icon-[tabler--folder] size-3.5"></span> {{ $quiz->category?->name ?? 'General' }}
        </span>
        <span class="badge {{ $status[1] }}"><span class="{{ $status[2] }} size-3.5"></span> {{ $status[0] }}</span>
    </div>

    <h3 class="font-display text-lg leading-snug font-bold text-white transition group-hover:text-brand-300">{{ $quiz->title }}</h3>
    @if ($quiz->description)
        <p class="mt-1.5 line-clamp-2 text-sm text-ink-400">{{ $quiz->description }}</p>
    @endif

    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-medium text-ink-400">
        <span class="flex items-center gap-1"><span class="icon-[tabler--list-numbers] size-4 text-ink-400"></span> {{ $result['total_questions'] }} Questions</span>
        <span class="flex items-center gap-1"><span class="icon-[tabler--hourglass] size-4 text-ink-400"></span> {{ ceil($result['total_questions'] * 45 / 60) }} min</span>
        @if (!$result['attempted'] && $start)
            <span class="flex items-center gap-1"><span class="icon-[tabler--calendar-event] size-4 text-ink-400"></span> {{ $start->format('d M, h:i A') }}</span>
        @endif
    </div>

    <div class="mt-auto pt-5">
        @if ($result['attempted'])
            <div class="mb-4 rounded-2xl bg-white/5 p-3">
                <div class="mb-2 flex items-center justify-between text-xs font-semibold">
                    <span class="text-ink-400">Accuracy</span>
                    <span class="text-white">{{ $percent }}%</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ $percent }}%"></div>
                </div>
                <div class="mt-3 flex justify-between text-xs">
                    <span class="flex items-center gap-1 font-semibold text-emerald-400"><span class="icon-[tabler--check] size-4"></span> {{ $result['right'] }} Right</span>
                    <span class="flex items-center gap-1 font-semibold text-rose-500"><span class="icon-[tabler--x] size-4"></span> {{ $result['wrong'] }} Wrong</span>
                    <span class="font-bold text-white">{{ $result['marks'] }} Marks</span>
                </div>
            </div>
            <a href="{{ route('user.quiz.result', $quiz->slug) }}" class="btn btn-dark w-full">
                <span class="icon-[tabler--report-analytics] size-4"></span> View Result
            </a>
        @else
            <a href="{{ route('user.quiz.show', $quiz->slug) }}" class="btn btn-primary w-full">
                Take Quiz <span class="icon-[tabler--arrow-right] size-4 transition group-hover:translate-x-1"></span>
            </a>
        @endif
    </div>
</article>
