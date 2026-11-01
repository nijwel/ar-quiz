@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <x-page-hero icon="icon-[tabler--category]" title="Categories" subtitle="কুইজের সকল ক্যাটেগরি এক জায়গায়।" />

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $key => $category)
                        <tr>
                            <td class="font-semibold text-ink-400">{{ $categories->firstItem() + $key }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid size-9 place-items-center rounded-xl bg-fuchsia-500/10 font-display text-sm font-bold text-fuchsia-400">
                                        {{ strtoupper(mb_substr($category->name, 0, 1)) }}
                                    </span>
                                    <span class="font-semibold text-white">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td><code class="rounded-lg bg-white/5 px-2 py-1 text-xs text-ink-300">{{ $category->slug }}</code></td>
                            <td class="text-center">
                                @if ($category->status == 1)
                                    <span class="badge bg-emerald-500/10 text-emerald-300"><span class="size-1.5 rounded-full bg-emerald-500"></span> Active</span>
                                @else
                                    <span class="badge bg-white/10 text-ink-400"><span class="size-1.5 rounded-full bg-ink-400"></span> Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('category.edit', $category->id) }}" class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--pencil] size-3.5"></span> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-14 text-center text-ink-400">কোনো ক্যাটেগরি পাওয়া যায়নি।</td>
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
