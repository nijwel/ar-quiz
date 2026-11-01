@extends('layouts.app')

@section('title', 'Participants')

@section('content')
    <x-page-hero icon="icon-[tabler--users-group]" title="Participants"
        subtitle="সিস্টেমে নিবন্ধিত সকল পরীক্ষার্থীদের তালিকা।">
        <x-slot:actions>
            <div class="rounded-2xl bg-white/10 px-5 py-3 text-right ring-1 ring-white/15">
                <p class="text-[11px] font-bold tracking-wider text-ink-400 uppercase">Total</p>
                <p class="font-display text-2xl font-bold">{{ $participants->total() }}</p>
            </div>
        </x-slot:actions>
    </x-page-hero>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Participant</th>
                        <th>Email</th>
                        <th class="text-center">Total Marks</th>
                        <th class="text-center">Exams Given</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($participants as $key => $participant)
                        <tr>
                            <td class="font-semibold text-ink-400">{{ $participants->firstItem() + $key }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 font-display font-bold text-snow">
                                        {{ strtoupper(mb_substr($participant->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-white">{{ $participant->name }}</p>
                                        <p class="text-xs text-ink-400">Joined {{ $participant->created_at->format('M Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-ink-400">{{ $participant->email }}</td>
                            <td class="text-center">
                                <span class="font-display text-lg font-bold text-emerald-400">{{ (int) $participant->total_marks }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-brand-500/10 text-brand-300">
                                    <span class="icon-[tabler--file-text] size-3.5"></span> {{ $participant->quizzes_attempted_count }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('participant.view', $participant->id) }}" class="btn btn-dark btn-sm">
                                    <span class="icon-[tabler--eye] size-3.5"></span> Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center text-ink-400">এখনো কোনো পরীক্ষার্থী নেই।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-6 py-4">
            <p class="text-sm text-ink-400">
                Showing <b class="text-white">{{ $participants->count() }}</b> of
                <b class="text-white">{{ $participants->total() }}</b> participants
            </p>
            {{ $participants->links() }}
        </div>
    </div>
@endsection
