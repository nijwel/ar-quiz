@php
    $quiz = $quiz ?? null;
    $toInput = fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '';

    if (old('questions')) {
        $initialQuestions = collect(old('questions'))->map(fn($q) => [
            'text' => $q['text'] ?? '',
            'answers' => array_values($q['answers'] ?? ['', '', '', '']),
            'correct' => (int) ($q['correct'] ?? 0),
        ])->values();
    } elseif ($quiz) {
        $initialQuestions = $quiz->questions->map(fn($q) => [
            'text' => $q->question,
            'answers' => $q->answers->pluck('answer')->values(),
            'correct' => max(0, (int) $q->answers->search(fn($a) => $a->is_correct)),
        ])->values();
    } else {
        $initialQuestions = [['text' => '', 'answers' => ['', '', '', ''], 'correct' => 0]];
    }
@endphp

<form action="{{ $action }}" method="POST" x-data="quizBuilder(@js($initialQuestions))">
    @csrf
    @if ($quiz)
        @method('PUT')
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="space-y-6">
            {{-- Quiz details --}}
            <div class="card p-6">
                <h2 class="mb-5 flex items-center gap-2 font-display text-lg font-bold text-white">
                    <span class="icon-[tabler--info-square-rounded] size-5 text-brand-400"></span> Quiz Details
                </h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label" for="title">Quiz Title</label>
                        <input id="title" type="text" name="title" value="{{ old('title', $quiz?->title) }}"
                            class="input @error('title') input-error @enderror" placeholder="e.g. Basic Laravel Quiz" required>
                        @error('title')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="description">Description</label>
                        <textarea id="description" name="description" rows="3" class="input"
                            placeholder="এই কুইজটি কী নিয়ে, সংক্ষেপে লিখুন...">{{ old('description', $quiz?->description) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="input" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $quiz?->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="start_exam_at">Start Date & Time</label>
                        <input id="start_exam_at" type="datetime-local" name="start_exam_at"
                            value="{{ old('start_exam_at', $toInput($quiz?->start_exam_at)) }}" class="input" required>
                    </div>
                    <div>
                        <label class="label" for="end_exam_at">End Date & Time</label>
                        <input id="end_exam_at" type="datetime-local" name="end_exam_at"
                            value="{{ old('end_exam_at', $toInput($quiz?->end_exam_at)) }}"
                            class="input @error('end_exam_at') input-error @enderror" required>
                        @error('end_exam_at')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            <template x-for="(question, qIndex) in questions" :key="question.uid">
                <div class="card animate-rise p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-xl bg-white/10 font-display text-sm font-bold text-volt-400"
                                x-text="String(qIndex + 1).padStart(2, '0')"></span>
                            <span class="font-display font-bold text-white">Question</span>
                        </span>
                        <button type="button" x-show="questions.length > 1" @click="removeQuestion(qIndex)"
                            class="btn btn-danger btn-sm">
                            <span class="icon-[tabler--trash] size-3.5"></span> Remove
                        </button>
                    </div>

                    <input type="text" :name="`questions[${qIndex}][text]`" x-model="question.text"
                        class="input mb-5 text-base font-medium" placeholder="প্রশ্নটি লিখুন..." required>

                    <p class="mb-3 text-xs font-bold tracking-wider text-ink-400 uppercase">
                        Options <span class="font-medium tracking-normal normal-case">— সঠিক উত্তরটি সিলেক্ট করুন</span>
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <template x-for="(answer, aIndex) in question.answers" :key="aIndex">
                            <div class="group relative flex items-center gap-2 rounded-2xl p-2 ring-1 transition"
                                :class="question.correct === aIndex ? 'bg-emerald-500/10 ring-emerald-500/40' : 'bg-white/5 ring-white/10'">
                                <label class="grid size-9 shrink-0 cursor-pointer place-items-center rounded-xl text-sm font-bold transition"
                                    :class="question.correct === aIndex ? 'bg-emerald-500 text-snow' : 'bg-white/5 text-ink-400 ring-1 ring-white/10 hover:ring-emerald-500/40'"
                                    title="Mark as correct">
                                    <input type="radio" class="sr-only" :name="`questions[${qIndex}][correct]`" :value="aIndex"
                                        :checked="question.correct === aIndex" @change="question.correct = aIndex">
                                    <span x-show="question.correct !== aIndex" x-text="String.fromCharCode(65 + aIndex)"></span>
                                    <span x-show="question.correct === aIndex" class="icon-[tabler--check] size-5"></span>
                                </label>
                                <input type="text" :name="`questions[${qIndex}][answers][${aIndex}]`" x-model="question.answers[aIndex]"
                                    class="min-w-0 flex-1 bg-transparent px-1 py-1.5 text-sm text-white placeholder:text-ink-400 focus:outline-none"
                                    :placeholder="`Option ${aIndex + 1}`" required>
                                <button type="button" x-show="question.answers.length > 2" @click="removeAnswer(question, aIndex)"
                                    class="grid size-7 shrink-0 place-items-center rounded-lg text-ink-300 opacity-0 transition group-hover:opacity-100 hover:bg-rose-500/10 hover:text-rose-500">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-show="question.answers.length < 6" @click="question.answers.push('')"
                        class="mt-3 flex items-center gap-1.5 text-sm font-semibold text-brand-400 hover:text-brand-300">
                        <span class="icon-[tabler--plus] size-4"></span> Add option
                    </button>
                </div>
            </template>

            <button type="button" @click="addQuestion()"
                class="flex w-full items-center justify-center gap-2 rounded-3xl border-2 border-dashed border-white/10 py-6 font-semibold text-ink-400 transition hover:border-brand-400 hover:bg-brand-500/10 hover:text-brand-400">
                <span class="icon-[tabler--square-rounded-plus] size-5"></span> Add Another Question
            </button>
        </div>

        {{-- Sticky summary --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden">
                <div class="border-b border-white/10 bg-gradient-to-br from-brand-600/30 to-transparent p-6 text-white">
                    <p class="text-xs font-bold tracking-[0.2em] text-volt-400 uppercase">Summary</p>
                    <p class="mt-3 font-display text-5xl font-extrabold" x-text="questions.length"></p>
                    <p class="text-sm text-ink-400">questions in this quiz</p>
                </div>
                <div class="space-y-3 p-6 text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-400">Estimated time</span>
                        <span class="font-semibold text-white" x-text="`${Math.ceil(questions.length * 45 / 60)} min`"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-400">Total options</span>
                        <span class="font-semibold text-white" x-text="questions.reduce((n, q) => n + q.answers.length, 0)"></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg mt-3 w-full">
                        <span class="icon-[tabler--device-floppy] size-5"></span> {{ $submitLabel }}
                    </button>
                    <a href="{{ route('quiz.index') }}" class="btn btn-ghost w-full">Cancel</a>
                </div>
            </div>
        </aside>
    </div>
</form>

@push('scripts')
    <script>
        function quizBuilder(initial) {
            let uid = 0;
            const make = (q = {}) => ({
                uid: ++uid,
                text: q.text ?? '',
                answers: q.answers?.length ? [...q.answers] : ['', '', '', ''],
                correct: q.correct ?? 0,
            });

            return {
                questions: initial.map(make),
                addQuestion() {
                    this.questions.push(make());
                    this.$nextTick(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }));
                },
                removeQuestion(index) {
                    if (confirm('আপনি কি এই প্রশ্নটি মুছে ফেলতে চান?')) this.questions.splice(index, 1);
                },
                removeAnswer(question, index) {
                    question.answers.splice(index, 1);
                    if (question.correct === index) question.correct = 0;
                    else if (question.correct > index) question.correct--;
                },
            };
        }
    </script>
@endpush
