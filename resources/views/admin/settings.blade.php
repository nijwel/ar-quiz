@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <x-page-hero icon="icon-[tabler--settings]" title="Exam Settings" subtitle="পরীক্ষার সময় ও কতবার দেওয়া যাবে তা এখান থেকে নিয়ন্ত্রণ করুন।" />

    {{-- Branding --}}
    <form action="{{ route('admin.settings.branding') }}" method="POST" enctype="multipart/form-data"
        class="card mx-auto mb-6 max-w-4xl p-6">
        @csrf
        @method('PUT')

        <div class="mb-6 flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-2xl bg-fuchsia-500/10 text-fuchsia-300 ring-1 ring-fuchsia-500/25">
                <span class="icon-[tabler--palette] size-6"></span>
            </span>
            <div>
                <h2 class="font-display text-lg font-bold text-white">Branding</h2>
                <p class="text-sm text-ink-400">লোগো ও ফেভিকন আপলোড করুন</p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            @foreach ([
                ['logo', 'Logo', $logoUrl, 'PNG, JPG বা WEBP · সর্বোচ্চ 2 MB · বর্গাকার হলে ভালো দেখায়', 'image/png,image/jpeg,image/webp', 'size-20'],
                ['favicon', 'Favicon', $faviconUrl, 'PNG বা ICO · সর্বোচ্চ 512 KB · 32×32 বা 64×64', 'image/png,image/x-icon,.ico', 'size-12'],
            ] as [$field, $label, $currentUrl, $hint, $accept, $previewSize])
                <div x-data="{ preview: @js($currentUrl), remove: false }">
                    <span class="label">{{ $label }}</span>
                    <div class="flex items-center gap-4 rounded-2xl bg-white/5 p-4 ring-1 ring-white/10">
                        <div class="grid size-20 shrink-0 place-items-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                            <template x-if="preview && !remove">
                                <img :src="preview" alt="" class="{{ $previewSize }} rounded-xl object-contain">
                            </template>
                            <template x-if="!preview || remove">
                                {{-- Default mark shown when nothing is uploaded --}}
                                <span class="grid size-12 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-800 text-snow">
                                    <span class="icon-[tabler--bolt-filled] size-6"></span>
                                </span>
                            </template>
                        </div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <label class="btn btn-ghost btn-sm cursor-pointer">
                                <span class="icon-[tabler--upload] size-4"></span> Choose file
                                <input type="file" name="{{ $field }}" accept="{{ $accept }}" class="sr-only"
                                    @change="const file = $event.target.files[0]; if (file) { preview = URL.createObjectURL(file); remove = false }">
                            </label>
                            <p class="text-xs text-ink-400">{{ $hint }}</p>
                            @if ($currentUrl)
                                <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-rose-400">
                                    <input type="checkbox" name="remove_{{ $field }}" value="1" x-model="remove" class="size-3.5 accent-rose-500">
                                    Remove current {{ strtolower($label) }}
                                </label>
                            @endif
                        </div>
                    </div>
                    @error($field)
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--device-floppy] size-4"></span> Save Branding
            </button>
        </div>
    </form>

    <form action="{{ route('admin.settings.update') }}" method="POST"
        x-data="{ seconds: {{ old('seconds_per_question', $secondsPerQuestion) }}, attempts: {{ old('max_attempts', $maxAttempts) }} }"
        class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
        @csrf
        @method('PUT')

        {{-- Time per question --}}
        <div class="card flex flex-col p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-2xl bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/25">
                    <span class="icon-[tabler--hourglass] size-6"></span>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-white">Time per Question</h2>
                    <p class="text-sm text-ink-400">প্রতিটি প্রশ্নের জন্য ডিফল্ট সময়</p>
                </div>
            </div>

            <label class="label" for="seconds_per_question">Seconds per question</label>
            <div class="relative">
                <input id="seconds_per_question" type="number" name="seconds_per_question" min="5" max="3600" x-model.number="seconds"
                    class="input pr-20 text-lg font-bold @error('seconds_per_question') input-error @enderror" required>
                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-sm text-ink-400">seconds</span>
            </div>
            @error('seconds_per_question')
                <p class="field-error">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ([30, 45, 60, 90, 120] as $preset)
                    <button type="button" @click="seconds = {{ $preset }}"
                        :class="seconds === {{ $preset }} ? 'bg-brand-600 text-snow' : 'bg-white/5 text-ink-300 ring-1 ring-white/10 hover:text-white'"
                        class="rounded-lg px-2.5 py-1 text-xs font-semibold transition">{{ $preset }}s</button>
                @endforeach
            </div>

            <div class="mt-auto pt-5">
                <div class="rounded-2xl bg-white/5 p-4 text-sm">
                    <p class="text-ink-400">উদাহরণ: ১০টি প্রশ্নের পরীক্ষা হবে</p>
                    <p class="mt-1 font-display text-2xl font-bold text-white"
                        x-text="`${Math.floor(seconds * 10 / 60)} min ${seconds * 10 % 60 ? (seconds * 10 % 60) + ' sec' : ''}`"></p>
                    <p class="mt-2 text-xs text-ink-500">যে পরীক্ষায় আলাদা "Exam Duration" দেওয়া আছে, সেখানে সেটিই প্রযোজ্য হবে।</p>
                </div>
            </div>
        </div>

        {{-- Attempts --}}
        <div class="card flex flex-col p-6">
            <div class="mb-5 flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-2xl bg-amber-500/10 text-amber-300 ring-1 ring-amber-500/25">
                    <span class="icon-[tabler--repeat] size-6"></span>
                </span>
                <div>
                    <h2 class="font-display text-lg font-bold text-white">Attempts per Exam</h2>
                    <p class="text-sm text-ink-400">একজন স্টুডেন্ট একই পরীক্ষা কতবার দিতে পারবে</p>
                </div>
            </div>

            <label class="label" for="max_attempts">Maximum attempts</label>
            <div class="flex items-center gap-2">
                <button type="button" @click="attempts = Math.max(1, attempts - 1)" class="btn btn-ghost btn-icon size-12">
                    <span class="icon-[tabler--minus] size-5"></span>
                </button>
                <input id="max_attempts" type="number" name="max_attempts" min="1" max="100" x-model.number="attempts"
                    class="input text-center text-lg font-bold @error('max_attempts') input-error @enderror" required>
                <button type="button" @click="attempts = Math.min(100, attempts + 1)" class="btn btn-ghost btn-icon size-12">
                    <span class="icon-[tabler--plus] size-5"></span>
                </button>
            </div>
            @error('max_attempts')
                <p class="field-error">{{ $message }}</p>
            @enderror

            <div class="mt-auto pt-5">
                <div class="rounded-2xl bg-white/5 p-4 text-sm">
                    <p class="font-semibold text-white"
                        x-text="attempts === 1 ? 'প্রতিটি পরীক্ষা মাত্র একবার দেওয়া যাবে।' : `প্রতিটি পরীক্ষা সর্বোচ্চ ${attempts} বার দেওয়া যাবে।`"></p>
                    <p class="mt-2 text-xs text-ink-500">স্কোর ও লিডারবোর্ডে প্রতিটি পরীক্ষার সর্বশেষ চেষ্টাটি গণনা করা হবে।</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end md:col-span-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <span class="icon-[tabler--device-floppy] size-5"></span> Save Settings
            </button>
        </div>
    </form>
@endsection
