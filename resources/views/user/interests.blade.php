@extends('layouts.app')

@section('title', 'আমার বিষয়')

@section('content')
    @php
        // group id => its sub-category ids, so the picker knows what a group covers
        $groupChildren = $groups->mapWithKeys(fn($group) => [$group->id => $group->children->pluck('id')]);
    @endphp

    <x-page-hero icon="icon-[tabler--tags]" :eyebrow="$firstVisit ? 'স্বাগতম, ' . auth()->user()->name . '!' : null"
        title="আপনি কোন কোন বিষয়ে পরীক্ষা দিতে চান?"
        subtitle="সর্বোচ্চ {{ $max }}টি বিষয় বেছে নিন। শুধু বেছে নেওয়া বিষয়ের পরীক্ষাই আপনি দেখতে ও দিতে পারবেন। পরে প্রোফাইল থেকে যেকোনো সময় বদলানো যাবে।" />

    <form action="{{ route('user.interests.update') }}" method="POST"
        x-data="interestPicker(@js($selected), {{ $max }}, @js($groupChildren))" class="pb-28">
        @csrf
        @method('PUT')

        <template x-for="id in selected" :key="id">
            <input type="hidden" name="interests[]" :value="id">
        </template>

        @error('interests')
            <p class="field-error mb-4 text-sm">{{ $message }}</p>
        @enderror

        {{-- Groups with sub-categories, e.g. Programming --}}
        @foreach ($groups as $group)
            <section class="card mb-5 p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-bold text-white">{{ $group->name }}</h2>
                        <p class="text-xs text-ink-400">{{ $group->children->count() }}টি বিষয় · {{ $group->exam_count }}টি পরীক্ষা</p>
                    </div>
                    <button type="button" @click="toggleGroup({{ $group->id }})"
                        :class="has({{ $group->id }}) ? 'bg-brand-600 text-snow ring-brand-500 shadow-lg shadow-brand-600/30' : 'bg-white/5 text-ink-200 ring-white/15 hover:ring-brand-500/50'"
                        class="badge px-4 py-2 text-sm ring-1 transition">
                        <span :class="has({{ $group->id }}) ? 'icon-[tabler--circle-check-filled]' : 'icon-[tabler--circle-plus]'" class="size-4"></span>
                        পুরো {{ $group->name }}
                    </button>
                </div>

                <p class="mb-3 text-xs text-ink-500"
                    x-text="has({{ $group->id }}) ? 'পুরো গ্রুপ বেছে নেওয়া হয়েছে, নিচের সব বিষয় এর মধ্যে আছে।' : 'অথবা শুধু নির্দিষ্ট বিষয়গুলো বেছে নিন:'"></p>

                <div class="flex flex-wrap gap-2">
                    @foreach ($group->children as $child)
                        <button type="button" @click="toggle({{ $child->id }})" :disabled="has({{ $group->id }})"
                            :class="has({{ $group->id }}) ? 'bg-brand-500/10 text-brand-300 ring-brand-500/25 cursor-default'
                                : (has({{ $child->id }}) ? 'bg-brand-600 text-snow ring-brand-500' : 'bg-white/5 text-ink-300 ring-white/10 hover:ring-white/25 hover:text-white')"
                            class="badge px-3.5 py-2 text-sm ring-1 transition">
                            <span :class="has({{ $child->id }}) || has({{ $group->id }}) ? 'icon-[tabler--check]' : 'icon-[tabler--plus]'" class="size-3.5"></span>
                            {{ $child->name }}
                            <span class="opacity-60">{{ $child->quizzes_count }}</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Stand-alone subjects --}}
        @if ($singles->isNotEmpty())
            <section class="card p-5 sm:p-6">
                <h2 class="mb-4 font-display text-lg font-bold text-white">অন্যান্য বিষয়</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($singles as $subject)
                        <button type="button" @click="toggle({{ $subject->id }})"
                            :class="has({{ $subject->id }}) ? 'bg-brand-600/15 ring-2 ring-brand-500' : 'bg-white/5 ring-1 ring-white/10 hover:ring-white/25'"
                            class="flex items-center gap-3 rounded-2xl p-4 text-left transition">
                            <span :class="has({{ $subject->id }}) ? 'bg-brand-600 text-snow' : 'bg-white/10 text-ink-400'"
                                class="grid size-9 shrink-0 place-items-center rounded-xl transition">
                                <span :class="has({{ $subject->id }}) ? 'icon-[tabler--check]' : 'icon-[tabler--plus]'" class="size-5"></span>
                            </span>
                            <span>
                                <span class="block font-semibold text-white">{{ $subject->name }}</span>
                                <span class="text-xs text-ink-400">{{ $subject->exam_count }}টি পরীক্ষা</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Sticky save bar --}}
        <div class="fixed inset-x-3 bottom-20 z-30 mx-auto max-w-3xl md:bottom-4">
            <div class="flex items-center gap-4 rounded-2xl glass-panel p-3 pl-5">
                <div class="flex-1">
                    <p class="font-display font-bold text-white">
                        <span x-text="selected.length"></span><span class="text-ink-400">/{{ $max }}</span> বিষয়
                    </p>
                    <p class="text-xs" :class="limitHit ? 'text-amber-400' : 'text-ink-400'"
                        x-text="limitHit ? 'সীমা পূর্ণ। নতুন বিষয় নিতে একটি বাদ দিন।' : (selected.length ? 'পুরো গ্রুপ একটি বিষয় হিসেবে গণ্য হয়।' : 'অন্তত একটি বিষয় বেছে নিন।')"></p>
                </div>
                <button type="submit" class="btn btn-primary" :disabled="!selected.length">
                    <span class="icon-[tabler--device-floppy] size-4"></span>
                    {{ $firstVisit ? 'শুরু করুন' : 'সেভ করুন' }}
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function interestPicker(selected, max, groupChildren) {
            return {
                selected: selected.map(Number),
                limitHit: false,

                has(id) {
                    return this.selected.includes(id);
                },

                toggle(id) {
                    if (this.has(id)) return this.remove([id]);
                    this.add(id);
                },

                // Choosing a whole group replaces any of its sub-categories chosen one by one
                toggleGroup(groupId) {
                    if (this.has(groupId)) return this.remove([groupId]);

                    const children = groupChildren[groupId] ?? [];
                    const replaced = this.selected.filter((id) => children.includes(id));
                    if (this.selected.length - replaced.length + 1 > max) return this.flagLimit();

                    this.remove(replaced);
                    this.add(groupId);
                },

                add(id) {
                    if (this.selected.length >= max) return this.flagLimit();
                    this.selected.push(id);
                    this.limitHit = false;
                },

                remove(ids) {
                    this.selected = this.selected.filter((id) => !ids.includes(id));
                    this.limitHit = false;
                },

                flagLimit() {
                    this.limitHit = true;
                },
            };
        }
    </script>
@endpush
