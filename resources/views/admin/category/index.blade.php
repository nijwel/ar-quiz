@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <x-page-hero icon="icon-[tabler--category]" title="Categories" subtitle="কুইজের সকল ক্যাটেগরি এক জায়গায়। গ্রুপের নিচে তার সাব-ক্যাটেগরি দেখানো হয়েছে।">
        <x-slot:actions>
            <a href="{{ route('category.create') }}" class="btn btn-volt">
                <span class="icon-[tabler--plus] size-4"></span> New Category
            </a>
        </x-slot:actions>
    </x-page-hero>

    <div class="card overflow-hidden" x-data="categoryBulk(@js($categories->pluck('quizzes_count', 'id')))">
        {{-- Bulk action bar; the row checkboxes belong to this form via form="bulkForm" --}}
        <form id="bulkForm" action="{{ route('category.bulk-status') }}" method="POST"
            x-show="selected.length" x-cloak x-transition @submit="setMethod($event)"
            class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 bg-brand-500/5 px-6 py-3">
            @csrf
            <input type="hidden" name="_method" value="PATCH" x-ref="method">
            <p class="text-sm text-ink-300"><b class="text-white" x-text="selected.length"></b> selected</p>
            <div class="flex items-center gap-2">
                <button type="submit" name="status" value="1" class="btn btn-sm bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-500/30 hover:bg-emerald-500/20">
                    <span class="icon-[tabler--eye] size-3.5"></span> Activate
                </button>
                <button type="submit" name="status" value="0" :data-confirm="hideMessage()" class="btn btn-sm bg-white/5 text-ink-300 ring-1 ring-white/15 hover:bg-white/10">
                    <span class="icon-[tabler--eye-off] size-3.5"></span> Deactivate
                </button>
                <button type="submit" data-action="delete" :data-confirm="deleteMessage()" data-confirm-tone="danger" formaction="{{ route('category.bulk-destroy') }}"
                    class="btn btn-sm bg-rose-500/10 text-rose-300 ring-1 ring-rose-500/30 hover:bg-rose-500/20">
                    <span class="icon-[tabler--trash] size-3.5"></span> Delete
                </button>
                <button type="button" @click="selected = []" class="btn btn-ghost btn-sm">Clear</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" class="size-4 rounded accent-brand-600" title="Select all on this page"
                                :checked="pageIds.length && selected.length === pageIds.length"
                                :indeterminate="selected.length > 0 && selected.length < pageIds.length"
                                @change="selected = $event.target.checked ? [...pageIds] : []">
                        </th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-center">Quizzes</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $key => $category)
                        <tr :class="selected.includes({{ $category->id }}) && 'bg-brand-500/5'">
                            <td>
                                <input type="checkbox" form="bulkForm" name="ids[]" value="{{ $category->id }}"
                                    x-model.number="selected" class="size-4 rounded accent-brand-600">
                            </td>
                            <td class="font-semibold text-ink-400">{{ $categories->firstItem() + $key }}</td>
                            <td>
                                <div class="flex items-center gap-3 {{ $category->parent_id ? 'pl-6' : '' }}">
                                    @if ($category->parent_id)
                                        <span class="icon-[tabler--corner-down-right] -ml-5 size-4 text-ink-500"></span>
                                    @endif
                                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-fuchsia-500/10 font-display text-sm font-bold text-fuchsia-400">
                                        {{ strtoupper(mb_substr($category->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 font-semibold text-white">
                                            {{ $category->name }}
                                            @if ($category->children_count)
                                                <span class="badge bg-brand-500/10 px-2 py-0.5 text-[10px] text-brand-300">Group · {{ $category->children_count }}</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-ink-500">
                                            @if ($category->parent)
                                                {{ $category->parent->name }} ·
                                            @endif
                                            {{ $category->students_count }} জন স্টুডেন্ট বেছে নিয়েছে
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td><code class="rounded-lg bg-white/5 px-2 py-1 text-xs text-ink-300">{{ $category->slug }}</code></td>
                            <td class="text-center">
                                <span class="font-semibold text-ink-300">{{ $category->total_quizzes_count }}</span>
                                @if ($category->children_count)
                                    <span class="block text-[10px] text-ink-500">{{ $category->children_count }}টি সাব-ক্যাটেগরি মিলে</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form action="{{ route('category.status', $category) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        title="{{ $category->status ? 'Click to deactivate' : 'Click to activate' }}"
                                        @if ($category->status && $category->quizzes_count)
                                            data-confirm="এই ক্যাটেগরির {{ $category->quizzes_count }}টি পরীক্ষা শিক্ষার্থীদের কাছে আর দেখা যাবে না।"
                                        @endif
                                        class="badge cursor-pointer transition hover:ring-1 {{ $category->status ? 'bg-emerald-500/10 text-emerald-300 hover:ring-emerald-500/40' : 'bg-white/10 text-ink-400 hover:ring-white/30' }}">
                                        <span class="size-1.5 rounded-full {{ $category->status ? 'bg-emerald-500' : 'bg-ink-400' }}"></span>
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                        <span class="icon-[tabler--switch-horizontal] size-3.5 opacity-60"></span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('category.edit', $category->id) }}" class="btn btn-ghost btn-sm">
                                        <span class="icon-[tabler--pencil] size-3.5"></span> Edit
                                    </a>
                                    @if ($category->quizzes_count)
                                        <span class="btn btn-ghost btn-icon cursor-not-allowed opacity-40"
                                            title="এই ক্যাটেগরিতে {{ $category->quizzes_count }}টি পরীক্ষা আছে, তাই মোছা যাবে না। চাইলে Inactive করুন।">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </span>
                                    @else
                                        <form action="{{ route('category.destroy', $category->id) }}" method="POST"
                                            data-confirm="“{{ $category->name }}” ক্যাটেগরিটি মুছে ফেলা হবে।" data-confirm-tone="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-icon text-rose-400 hover:text-rose-300" title="Delete category">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center text-ink-400">কোনো ক্যাটেগরি পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-6 py-4">
            <p class="text-sm text-ink-400">
                Showing <b class="text-white">{{ $categories->count() }}</b> of
                <b class="text-white">{{ $categories->total() }}</b> categories
            </p>
            {{ $categories->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // quizCounts: { categoryId: quizzes_count } for the categories on this page
            Alpine.data('categoryBulk', (quizCounts) => ({
                selected: [],
                pageIds: Object.keys(quizCounts).map(Number),

                withQuizzes() {
                    return this.selected.filter((id) => quizCounts[id] > 0).length;
                },

                // Bound to the buttons' data-confirm; null means no confirmation needed
                deleteMessage() {
                    const skipped = this.withQuizzes();
                    return `${this.selected.length}টি ক্যাটেগরি মুছে ফেলা হবে।` +
                        (skipped ? `\n${skipped}টিতে পরীক্ষা আছে, সেগুলো মুছবে না।` : '');
                },

                hideMessage() {
                    return this.withQuizzes() ? 'নির্বাচিত ক্যাটেগরিগুলোর পরীক্ষা শিক্ষার্থীদের কাছে আর দেখা যাবে না।' : null;
                },

                setMethod(event) {
                    this.$refs.method.value = event.submitter?.dataset.action === 'delete' ? 'DELETE' : 'PATCH';
                },
            }));
        });
    </script>
@endpush
