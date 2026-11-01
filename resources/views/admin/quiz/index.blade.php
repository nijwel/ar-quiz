@extends('layouts.app')

@section('title', 'Quizzes')

@section('content')
    <div x-data="{ upload: false }">
        <x-page-hero icon="icon-[tabler--file-text]" title="Quiz Management"
            subtitle="নতুন কুইজ তৈরি করুন অথবা JSON আপলোড করে কুইজ যোগ করুন।">
            <x-slot:actions>
                <button type="button" @click="upload = true" class="btn btn-glass">
                    <span class="icon-[tabler--cloud-upload] size-4"></span> Upload JSON
                </button>
                <a href="{{ route('quiz.create') }}" class="btn btn-volt">
                    <span class="icon-[tabler--plus] size-4"></span> Create New
                </a>
            </x-slot:actions>
        </x-page-hero>

        {{-- Category filter --}}
        <div class="mb-5 flex items-center gap-2 overflow-x-auto pb-1">
            <span class="mr-1 flex shrink-0 items-center gap-1 text-xs font-bold tracking-wider text-ink-400 uppercase">
                <span class="icon-[tabler--filter] size-4"></span> Filter
            </span>
            @php $current = request('category'); @endphp
            <a href="{{ route('quiz.index') }}"
                class="badge shrink-0 px-3.5 py-2 text-sm transition {{ !$current ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('quiz.index', ['category' => $category->id]) }}"
                    class="badge shrink-0 px-3.5 py-2 text-sm transition {{ $current == $category->id ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Quiz</th>
                            <th>Category</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quizzes as $quiz)
                            @php
                                $start = $quiz->start_exam_at ? \Carbon\Carbon::parse($quiz->start_exam_at) : null;
                                $end = $quiz->end_exam_at ? \Carbon\Carbon::parse($quiz->end_exam_at) : null;
                                if ($start && $start->isFuture()) {
                                    $status = ['Upcoming', 'bg-sky-500/10 text-sky-300', 'bg-sky-500'];
                                } elseif ($end && $end->isPast()) {
                                    $status = ['Ended', 'bg-white/10 text-ink-400', 'bg-ink-400'];
                                } elseif ($start && $end) {
                                    $status = ['Live', 'bg-amber-500/10 text-amber-300', 'bg-amber-500 animate-pulse'];
                                } else {
                                    $status = ['Open', 'bg-emerald-500/10 text-emerald-300', 'bg-emerald-500'];
                                }
                            @endphp
                            <tr>
                                <td class="max-w-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 font-display text-xs font-bold text-volt-400">
                                            #{{ $quiz->id }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-white">{{ $quiz->title }}</p>
                                            <p class="truncate text-xs text-ink-400">
                                                <span class="icon-[tabler--help-circle] -mt-0.5 size-3.5 align-middle"></span>
                                                {{ $quiz->questions_count }} Questions
                                                @if ($quiz->description)
                                                    · {{ Str::limit($quiz->description, 50) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-brand-500/10 text-brand-300">{{ $quiz->category?->name ?? '—' }}</span>
                                </td>
                                <td class="text-xs whitespace-nowrap text-ink-300">
                                    @if ($start || $end)
                                        <div class="flex items-center gap-1.5">
                                            <span class="icon-[tabler--player-play] size-3.5 text-emerald-500"></span>
                                            {{ $start?->format('d M Y, h:i A') ?? '—' }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span class="icon-[tabler--player-stop] size-3.5 text-rose-500"></span>
                                            {{ $end?->format('d M Y, h:i A') ?? '—' }}
                                        </div>
                                    @else
                                        <span class="text-ink-400">No schedule</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $status[1] }}"><span class="size-1.5 rounded-full {{ $status[2] }}"></span> {{ $status[0] }}</span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('quiz.edit', $quiz->id) }}" class="btn btn-ghost btn-icon" title="Edit Quiz">
                                            <span class="icon-[tabler--pencil] size-4"></span>
                                        </a>
                                        <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST"
                                            onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই কুইজটি ডিলিট করতে চান?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-icon" title="Delete Quiz">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <span class="icon-[tabler--file-off] mx-auto mb-2 block size-10 text-ink-300"></span>
                                    <p class="text-ink-400">কোনো কুইজ পাওয়া যায়নি।</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-6 py-4">
                <p class="text-sm text-ink-400">
                    Showing <b class="text-white">{{ $quizzes->count() }}</b> of <b class="text-white">{{ $quizzes->total() }}</b> quizzes
                </p>
                {{ $quizzes->withQueryString()->links() }}
            </div>
        </div>

        {{-- Upload modal --}}
        <div x-cloak x-show="upload" class="fixed inset-0 z-50 grid place-items-center p-4" role="dialog" aria-modal="true">
            <div x-show="upload" x-transition.opacity class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

            <div x-show="upload" x-transition.scale.95 x-data="{ tab: 'file', fileName: '' }"
                class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-ink-950/90 shadow-2xl ring-1 ring-white/10 backdrop-blur-2xl">
                <div class="flex items-center justify-between border-b border-white/10 bg-white/[0.03] px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-white/10">
                            <span class="icon-[tabler--file-code] size-5 text-volt-400"></span>
                        </span>
                        <div>
                            <h3 class="font-display text-lg font-bold">Import Quiz</h3>
                            <p class="text-xs text-ink-400">শুধু JSON ফরম্যাট সাপোর্টেড</p>
                        </div>
                    </div>
                    <button type="button" @click="upload = false" class="grid size-9 place-items-center rounded-xl hover:bg-white/10">
                        <span class="icon-[tabler--x] size-5"></span>
                    </button>
                </div>

                <form action="{{ route('quiz.upload') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-5 grid grid-cols-2 gap-1 rounded-xl bg-white/5 p-1">
                        <button type="button" @click="tab = 'file'"
                            :class="tab === 'file' ? 'bg-white/10 shadow text-white' : 'text-ink-400'"
                            class="flex items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold transition">
                            <span class="icon-[tabler--upload] size-4"></span> Upload File
                        </button>
                        <button type="button" @click="tab = 'paste'"
                            :class="tab === 'paste' ? 'bg-white/10 shadow text-white' : 'text-ink-400'"
                            class="flex items-center justify-center gap-2 rounded-lg py-2 text-sm font-semibold transition">
                            <span class="icon-[tabler--clipboard-text] size-4"></span> Paste JSON
                        </button>
                    </div>

                    <div x-show="tab === 'file'">
                        <label
                            class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-white/10 bg-white/5 px-6 py-10 text-center transition hover:border-brand-400 hover:bg-brand-500/10">
                            <span class="icon-[tabler--cloud-upload] mb-3 size-10 text-brand-500"></span>
                            <span class="text-sm font-semibold text-white" x-text="fileName || 'Click to choose a .json file'"></span>
                            <span class="mt-1 text-xs text-ink-400">Max one file</span>
                            <input type="file" name="quiz_file" accept="application/json,.json" class="sr-only"
                                @change="fileName = $event.target.files[0]?.name || ''">
                        </label>
                    </div>

                    <div x-show="tab === 'paste'" x-cloak>
                        <textarea name="quiz_json" rows="10" class="input font-mono text-xs"
                            placeholder='{
  "quiz": {
    "title": "Laravel Advanced Quiz",
    "category": "Programming",
    "description": "...",
    "questions": []
  }
}'></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="upload = false" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--file-import] size-4"></span> Import Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
