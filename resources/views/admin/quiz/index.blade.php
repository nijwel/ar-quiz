@extends('layouts.app')
@use('App\Enums\Difficulty')

@section('title', 'Quizzes')

@section('content')
    <div x-data="{ upload: {{ $errors->hasAny(['quiz_file', 'quiz_json']) ? 'true' : 'false' }} }">
        <x-page-hero icon="icon-[tabler--file-text]" title="Quiz Management"
            subtitle="নতুন কুইজ তৈরি করুন অথবা JSON আপলোড করে কুইজ যোগ করুন।">
            <x-slot:actions>
                <button type="button" @click="upload = true" class="btn btn-glass">
                    <span class="icon-[tabler--file-import] size-4"></span> Import
                </button>
                @php $filtered = request()->hasAny(['category', 'difficulty']); @endphp
                <a href="{{ route('quiz.export', request()->only('category', 'difficulty')) }}" class="btn btn-glass"
                    title="{{ $filtered ? 'Export the quizzes matching the current filters' : 'Export every quiz' }}">
                    <span class="icon-[tabler--file-export] size-4"></span>
                    {{ $filtered ? 'Export ' . $quizzes->total() . ' filtered' : 'Export All' }}
                </a>
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
            <a href="{{ route('quiz.index', request()->only('difficulty', 'per_page')) }}"
                class="badge shrink-0 px-3.5 py-2 text-sm transition {{ !$current ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('quiz.index', ['category' => $category->id] + request()->only('difficulty', 'per_page')) }}"
                    class="badge shrink-0 px-3.5 py-2 text-sm transition {{ $current == $category->id ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">
                    {{ $category->name }}
                    @unless ($category->status)
                        <span class="text-xs opacity-60">(inactive)</span>
                    @endunless
                </a>
            @endforeach
        </div>

        <div class="-mt-2 mb-5 flex items-center gap-2 overflow-x-auto pb-1">
            <span class="mr-1 flex shrink-0 items-center gap-1 text-xs font-bold tracking-wider text-ink-400 uppercase">
                <span class="icon-[tabler--chart-bar] size-4"></span> Level
            </span>
            @php $currentLevel = Difficulty::tryFrom((string) request('difficulty')); @endphp
            <a href="{{ route('quiz.index', request()->only('category', 'per_page')) }}"
                class="badge shrink-0 px-3.5 py-2 text-sm transition {{ !$currentLevel ? 'bg-brand-600 text-snow shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:ring-white/20' }}">All</a>
            @foreach (Difficulty::cases() as $level)
                <a href="{{ route('quiz.index', ['difficulty' => $level->value] + request()->only('category', 'per_page')) }}"
                    class="badge shrink-0 px-3.5 py-2 text-sm transition {{ $currentLevel === $level ? $level->solidClass() : $level->badgeClass() . ' hover:opacity-80' }}">
                    <span class="{{ $level->icon() }} size-4"></span> {{ $level->label() }}
                </a>
            @endforeach
        </div>

        <div class="card overflow-hidden" x-data="{ selected: [], pageIds: @js($quizzes->pluck('id')) }">
            {{-- Bulk show / hide / delete; the row checkboxes join this form through their form attribute --}}
            <form id="bulk-actions" action="{{ route('quiz.status.bulk') }}" method="POST" x-cloak x-show="selected.length"
                x-transition.opacity
                class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 bg-brand-500/10 px-6 py-3">
                @csrf
                <p class="text-sm text-white">
                    <b x-text="selected.length"></b> টি কুইজ সিলেক্ট করা হয়েছে
                    <button type="button" @click="selected = []" class="ml-2 text-xs text-ink-400 underline hover:text-white">Clear</button>
                </p>
                <div class="flex gap-2">
                    <button type="submit" name="status" value="1" class="btn btn-sm bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-500/30 hover:bg-emerald-500/25">
                        <span class="icon-[tabler--eye] size-4"></span> Show to students
                    </button>
                    <button type="submit" name="status" value="0" class="btn btn-sm bg-white/10 text-ink-300 ring-1 ring-white/15 hover:bg-white/15"
                        onclick="return confirm('সিলেক্ট করা কুইজগুলো শিক্ষার্থীদের কাছে আর দেখা যাবে না। নিশ্চিত?')">
                        <span class="icon-[tabler--eye-off] size-4"></span> Hide from students
                    </button>
                    <button type="submit" formaction="{{ route('quiz.destroy.bulk') }}" class="btn btn-danger btn-sm"
                        @click="confirm(selected.length + ' টি কুইজ, এর প্রশ্ন ও শিক্ষার্থীদের সব ফলাফল স্থায়ীভাবে ডিলিট হবে। নিশ্চিত?') || $event.preventDefault()">
                        <span class="icon-[tabler--trash] size-4"></span> Delete
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" class="size-4 cursor-pointer rounded accent-brand-600" title="Select all on this page"
                                    :checked="pageIds.length && selected.length === pageIds.length"
                                    @change="selected = $event.target.checked ? [...pageIds] : []">
                            </th>
                            <th>Quiz</th>
                            <th>Category / Level</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-center">Visibility</th>
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
                            <tr :class="selected.includes({{ $quiz->id }}) && 'bg-brand-500/5'">
                                <td>
                                    <input type="checkbox" form="bulk-actions" name="ids[]" value="{{ $quiz->id }}"
                                        x-model.number="selected" class="size-4 cursor-pointer rounded accent-brand-600">
                                </td>
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
                                    <div class="flex flex-col items-start gap-1.5">
                                        <span class="badge bg-brand-500/10 text-brand-300">{{ $quiz->category?->name ?? '—' }}</span>
                                        <span class="badge {{ $quiz->difficulty->badgeClass() }}">
                                            <span class="{{ $quiz->difficulty->icon() }} size-3.5"></span> {{ $quiz->difficulty->label() }}
                                        </span>
                                    </div>
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
                                    <p class="mt-1.5 flex items-center gap-1 text-xs whitespace-nowrap text-ink-400"
                                        title="{{ $quiz->duration_minutes ? 'Fixed exam duration' : 'Questions × seconds per question (Settings)' }}">
                                        <span class="icon-[tabler--hourglass] size-3.5"></span>
                                        {{ $quiz->duration_minutes ? $quiz->duration_minutes . ' min' : 'Auto · ' . ceil($quiz->examSeconds($quiz->questions_count) / 60) . ' min' }}
                                    </p>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('quiz.status', $quiz) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            title="{{ $quiz->status ? 'Click to hide from students' : 'Click to show to students' }}"
                                            class="badge cursor-pointer whitespace-nowrap transition hover:ring-1 {{ $quiz->status ? 'bg-emerald-500/10 text-emerald-300 hover:ring-emerald-500/40' : 'bg-white/10 text-ink-400 hover:ring-white/30' }}">
                                            <span class="{{ $quiz->status ? 'icon-[tabler--eye]' : 'icon-[tabler--eye-off]' }} size-3.5"></span>
                                            {{ $quiz->status ? 'Shown' : 'Hidden' }}
                                            <span class="icon-[tabler--switch-horizontal] size-3.5 opacity-60"></span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('quiz.export.single', $quiz->id) }}" class="btn btn-ghost btn-icon" title="Export this quiz">
                                            <span class="icon-[tabler--download] size-4"></span>
                                        </a>
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
                                <td colspan="7" class="py-16 text-center">
                                    <span class="icon-[tabler--file-off] mx-auto mb-2 block size-10 text-ink-300"></span>
                                    <p class="text-ink-400">কোনো কুইজ পাওয়া যায়নি।</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-6 py-4">
                <div class="flex flex-wrap items-center gap-4">
                    <p class="text-sm text-ink-400">
                        Showing <b class="text-white">{{ $quizzes->count() }}</b> of <b class="text-white">{{ $quizzes->total() }}</b> quizzes
                    </p>
                    {{-- Page size; changing it goes back to page 1 and keeps the filters --}}
                    <form action="{{ route('quiz.index') }}" method="GET" class="flex items-center gap-2 text-sm text-ink-400">
                        @foreach (request()->only('category', 'difficulty') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="per_page">Per page</label>
                        <select id="per_page" name="per_page" onchange="this.form.submit()" class="input w-auto py-1.5 pr-8 text-sm">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" @selected($option === $perPage)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
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
                            <h3 class="font-display text-lg font-bold">Import Quizzes</h3>
                            <p class="text-xs text-ink-400">একটি বা একাধিক কুইজ, JSON ফরম্যাটে</p>
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
                            <span class="mt-1 text-xs text-ink-400">একটি কুইজ বা Export করা পুরো ফাইল · সর্বোচ্চ 20 MB</span>
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
    "difficulty": "easy | standard | hard | expert",
    "duration_minutes": 30,
    "description": "...",
    "questions": []
  }
}'></textarea>
                    </div>

                    @if ($errors->hasAny(['quiz_file', 'quiz_json']))
                        <ul class="mt-4 max-h-40 space-y-1 overflow-y-auto rounded-xl bg-rose-500/10 p-3 text-xs text-rose-300 ring-1 ring-rose-500/25">
                            @foreach ([...$errors->get('quiz_file'), ...$errors->get('quiz_json')] as $error)
                                <li class="flex gap-1.5"><span class="icon-[tabler--alert-circle] mt-px size-3.5 shrink-0"></span> {{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <ul class="mt-4 space-y-1 text-xs text-ink-400">
                        <li class="flex gap-1.5"><span class="icon-[tabler--info-circle] mt-px size-3.5 shrink-0"></span> কোনো কুইজে ভুল থাকলে কিছুই সেভ হবে না।</li>
                        <li class="flex gap-1.5"><span class="icon-[tabler--info-circle] mt-px size-3.5 shrink-0"></span> একই নামের কুইজ আগে থেকে থাকলে সেটি বাদ দেওয়া হবে।</li>
                        <li class="flex gap-1.5"><span class="icon-[tabler--info-circle] mt-px size-3.5 shrink-0"></span> ক্যাটেগরি না থাকলে স্বয়ংক্রিয়ভাবে তৈরি হবে।</li>
                    </ul>

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
