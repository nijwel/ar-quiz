@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    @include('partials.profile', [
        'roleLabel' => 'Student #' . auth()->user()->student_id,
        'profileRoute' => route('user.update.profile'),
        'passwordRoute' => route('user.update.password'),
        'emailEditable' => false,
    ])

    {{-- Subjects the student takes exams in --}}
    <section class="card mx-auto mt-6 max-w-4xl p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="flex items-center gap-2 font-display text-lg font-bold text-white">
                    <span class="icon-[tabler--tags] size-5 text-brand-400"></span> আমার বিষয়
                </h2>
                <p class="text-sm text-ink-400">শুধু এই বিষয়গুলোর পরীক্ষা আপনি দিতে পারবেন।</p>
            </div>
            <a href="{{ route('user.interests.edit') }}" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--pencil] size-3.5"></span> বদলান
            </a>
        </div>

        <div class="flex flex-wrap gap-2">
            @forelse ($interests as $interest)
                <span class="badge bg-brand-500/10 px-3.5 py-2 text-sm text-brand-300 ring-1 ring-brand-500/25"
                    @if ($interest->children->isNotEmpty()) title="{{ $interest->children->pluck('name')->implode(', ') }}" @endif>
                    {{ $interest->name }}
                    @if ($interest->children->isNotEmpty())
                        <span class="opacity-70">· সব {{ $interest->children->count() }}টি</span>
                    @endif
                </span>
            @empty
                <p class="text-sm text-ink-400">এখনো কোনো বিষয় বেছে নেওয়া হয়নি।</p>
            @endforelse
        </div>
    </section>
@endsection
