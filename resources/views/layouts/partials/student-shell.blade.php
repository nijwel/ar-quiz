@php
    $studentNav = [
        ['Home', route('home'), 'user/home', 'icon-[tabler--smart-home]'],
        ['Exams', route('user.quiz.index'), 'user/quizzes*', 'icon-[tabler--file-pencil]'],
        ['My Exams', route('user.my.quiz'), 'user/my-quiz', 'icon-[tabler--checklist]'],
        ['Leaderboard', route('user.quiz.leaderboard'), 'user/leader/board', 'icon-[tabler--trophy]'],
    ];
    $user = Auth::user();
@endphp

<div class="relative min-h-full overflow-x-clip">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-0 h-80 bg-dots [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>

    <header class="sticky top-0 z-40 px-3 pt-3 sm:px-6">
        <div
            class="mx-auto flex h-16 max-w-6xl items-center gap-4 rounded-2xl bg-black/40 px-3 shadow-[0_8px_30px_-12px_rgb(0_0_0/0.6)] ring-1 ring-white/10 backdrop-blur-xl sm:px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <x-brand-mark />
                <span class="font-display text-lg font-bold text-white">{{ config('app.name', 'QuizArena') }}</span>
            </a>

            <nav class="mx-auto hidden items-center gap-1 rounded-full bg-white/5 p-1 ring-1 ring-white/10 md:flex">
                @foreach ($studentNav as [$label, $href, $pattern, $icon])
                    <a href="{{ $href }}" class="top-link {{ request()->is($pattern) ? 'is-active' : '' }}">
                        <span class="{{ $icon }} size-4.5"></span> {{ $label }}
                    </a>
                @endforeach
            </nav>

            @include('layouts.partials.theme-toggle', ['class' => 'ml-auto md:ml-0'])

            <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                <button type="button" @click="open = !open"
                    class="flex items-center gap-2 rounded-xl py-1 pr-2 pl-1 transition hover:bg-white/5">
                    <span
                        class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 font-display text-sm font-bold text-snow">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-left leading-tight lg:block">
                        <span class="block max-w-32 truncate text-sm font-semibold text-white">{{ $user->name }}</span>
                        @if ($user->student_id)
                            <span class="block text-[11px] font-medium text-ink-400">#{{ $user->student_id }}</span>
                        @endif
                    </span>
                    <span class="icon-[tabler--chevron-down] size-4 text-ink-400 transition" :class="open && 'rotate-180'"></span>
                </button>
                <div x-cloak x-show="open" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-56 rounded-2xl bg-ink-950/90 p-2 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl">
                    <a href="{{ route('user.profile') }}"
                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-ink-300 hover:bg-white/5 hover:text-white">
                        <span class="icon-[tabler--user-circle] size-4.5"></span> Profile
                    </a>
                    <button type="button" onclick="document.getElementById('logout-form').submit()"
                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-400 hover:bg-rose-500/10">
                        <span class="icon-[tabler--logout] size-4.5"></span> Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="relative mx-auto max-w-6xl px-4 pt-6 pb-32 sm:px-6 md:pb-12">
        @include('layouts.partials.flash')
        @yield('content')
    </main>

    {{-- Mobile dock --}}
    <nav
        class="fixed inset-x-3 bottom-3 z-40 flex items-center justify-around rounded-2xl bg-black/60 p-1.5 shadow-2xl ring-1 ring-white/10 backdrop-blur md:hidden">
        @foreach ([...$studentNav, ['Profile', route('user.profile'), 'user/profile*', 'icon-[tabler--user-circle]']] as [$label, $href, $pattern, $icon])
            @php $active = request()->is($pattern); @endphp
            <a href="{{ $href }}"
                class="flex flex-1 flex-col items-center gap-0.5 rounded-xl py-2 text-[10px] font-semibold transition {{ $active ? 'bg-volt-400 text-snow' : 'text-ink-400 hover:text-white' }}">
                <span class="{{ $icon }} size-5"></span>
                {{ $label === 'Leaderboard' ? 'Ranks' : $label }}
            </a>
        @endforeach
    </nav>
</div>
