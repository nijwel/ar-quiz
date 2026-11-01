@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
    @php
        $podium = $leaderboard->take(3);
        // Display order: 2nd, 1st, 3rd
        $podiumOrder = collect([1 => $podium->get(1), 0 => $podium->get(0), 2 => $podium->get(2)])->filter();
        $podiumStyle = [
            0 => ['h-36 sm:h-44', 'from-volt-300 to-volt-500 text-snow', 'icon-[tabler--crown]', 'text-volt-500'],
            1 => ['h-28 sm:h-32', 'from-ink-100 to-ink-400 text-ink-950', 'icon-[tabler--medal]', 'text-ink-400'],
            2 => ['h-24 sm:h-24', 'from-orange-400 to-orange-700 text-snow', 'icon-[tabler--medal]', 'text-amber-400'],
        ];
        $myIndex = $leaderboard->search(fn($row) => $row->user_id == Auth::id());
    @endphp

    <x-page-hero icon="icon-[tabler--trophy]" title="Performance Leaderboard"
        subtitle="গড় নম্বরের (Accuracy) ভিত্তিতে সেরা পারফর্মাররা।">
        <x-slot:actions>
            @if ($myIndex !== false)
                <div class="rounded-2xl bg-volt-400 px-5 py-3 text-right text-snow">
                    <p class="text-[11px] font-bold tracking-wider uppercase opacity-70">Your Rank</p>
                    <p class="font-display text-2xl font-extrabold">#{{ $myIndex + 1 }}</p>
                </div>
            @endif
        </x-slot:actions>
    </x-page-hero>

    @if ($podium->isNotEmpty())
        {{-- Podium --}}
        <div class="mb-8 flex items-end justify-center gap-3 sm:gap-5">
            @foreach ($podiumOrder as $rank => $row)
                @php [$height, $gradient, $icon, $iconTone] = $podiumStyle[$rank]; @endphp
                <div class="flex w-28 animate-rise flex-col items-center sm:w-40" style="animation-delay: {{ (2 - $rank) * 120 }}ms">
                    <span class="{{ $icon }} mb-1 size-7 {{ $iconTone }} {{ $rank === 0 ? 'animate-float' : '' }}"></span>
                    <span class="relative mb-2 grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-700 font-display text-xl font-bold text-snow shadow-lg ring-4 ring-black/60 sm:size-16">
                        {{ strtoupper(mb_substr($row->user->name, 0, 1)) }}
                    </span>
                    <p class="w-full truncate text-center text-sm font-bold text-white">{{ $row->user->name }}</p>
                    <p class="mb-2 text-xs font-semibold text-ink-400">{{ round($row->average_score, 1) }}%</p>
                    <div class="flex w-full items-start justify-center rounded-t-2xl bg-gradient-to-b pt-3 font-display text-3xl font-extrabold shadow-inner {{ $height }} {{ $gradient }}">
                        {{ $rank + 1 }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card overflow-hidden">
        <ul class="divide-y divide-white/5">
            @forelse ($leaderboard as $index => $row)
                @php
                    $avg = round($row->average_score, 1);
                    $isMe = Auth::id() == $row->user_id;
                @endphp
                <li @class([
                    'flex items-center gap-3 px-4 py-3.5 transition sm:gap-4 sm:px-6',
                    'bg-volt-500/10' => $isMe,
                    'hover:bg-white/5' => !$isMe,
                ])>
                    <span @class([
                        'grid size-9 shrink-0 place-items-center rounded-xl font-display text-sm font-bold',
                        'bg-volt-400 text-snow' => $index === 0,
                        'bg-white/10 text-white' => $index === 1,
                        'bg-amber-500/20 text-amber-300' => $index === 2,
                        'bg-white/5 text-ink-400' => $index > 2,
                    ])>{{ $index + 1 }}</span>

                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 font-display font-bold text-white">
                        {{ strtoupper(mb_substr($row->user->name, 0, 1)) }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 truncate font-semibold text-white">
                            {{ $row->user->name }}
                            @if ($isMe)
                                <span class="badge bg-white/10 px-2 py-0.5 text-[10px] text-volt-400">YOU</span>
                            @endif
                        </p>
                        <p class="text-xs text-ink-400">
                            {{ $row->total_quizzes }} exams ·
                            <span class="font-semibold text-emerald-400">{{ $row->total_correct }}</span>/{{ $row->total_attempted }} correct
                        </p>
                    </div>

                    <div class="w-24 shrink-0 text-right sm:w-40">
                        <p class="font-display text-lg font-bold text-white">{{ $avg }}%</p>
                        <div class="mt-1 hidden h-1.5 overflow-hidden rounded-full bg-white/10 sm:block">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600" style="width: {{ $avg }}%"></div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="py-16 text-center text-ink-400">এখনো কেউ পরীক্ষা দেয়নি।</li>
            @endforelse
        </ul>
    </div>
@endsection
