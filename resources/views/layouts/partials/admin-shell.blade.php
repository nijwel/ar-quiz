@php
    $adminNav = [
        'Overview' => [
            ['Dashboard', url('/admin/home'), 'admin/home', 'icon-[tabler--layout-dashboard]'],
        ],
        'Content' => [
            ['Quizzes', route('quiz.index'), ['admin/quiz', 'admin/quiz/edit/*'], 'icon-[tabler--file-text]'],
            ['Create Quiz', route('quiz.create'), 'admin/quiz/create', 'icon-[tabler--square-rounded-plus]'],
            ['Categories', route('category.index'), 'admin/category*', 'icon-[tabler--category]'],
        ],
        'People' => [
            ['Participants', route('participant.index'), 'admin/participants*', 'icon-[tabler--users-group]'],
            ['My Profile', route('admin.profile'), 'admin/profile*', 'icon-[tabler--user-circle]'],
        ],
        'System' => [
            ['Settings', route('admin.settings'), 'admin/settings*', 'icon-[tabler--settings]'],
        ],
    ];
    $user = Auth::user();
@endphp

<div x-data="{ sidebar: false }" class="min-h-full" @keydown.escape.window="sidebar = false">
    {{-- Mobile overlay --}}
    <div x-cloak x-show="sidebar" x-transition.opacity @click="sidebar = false"
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col overflow-hidden border-r border-white/10 bg-black/40 backdrop-blur-2xl px-4 py-5 text-white transition-transform duration-300 lg:translate-x-0">
        <div class="pointer-events-none absolute -top-24 -left-20 size-64 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-10 size-56 rounded-full bg-volt-400/10 blur-3xl"></div>

        <a href="{{ url('/admin/home') }}" class="relative mb-8 flex items-center gap-3 px-2">
            <x-brand-mark class="rounded-2xl" tone="bg-volt-400 shadow-lg shadow-volt-400/20" />
            <span>
                <span class="block font-display text-lg leading-none font-bold">{{ config('app.name', 'QuizArena') }}</span>
                <span class="text-[11px] font-semibold tracking-[0.2em] text-ink-400 uppercase">Admin Studio</span>
            </span>
            <button type="button" @click.stop="sidebar = false"
                class="ml-auto grid size-8 place-items-center rounded-lg text-ink-400 hover:bg-white/10 lg:hidden">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </a>

        <nav class="relative flex-1 space-y-6 overflow-y-auto">
            @foreach ($adminNav as $group => $items)
                <div>
                    <p class="mb-2 px-3.5 text-[10px] font-bold tracking-[0.2em] text-ink-400 uppercase">{{ $group }}</p>
                    <div class="space-y-1">
                        @foreach ($items as [$label, $href, $pattern, $icon])
                            <a href="{{ $href }}" class="side-link {{ request()->is(...(array) $pattern) ? 'is-active' : '' }}">
                                <span class="{{ $icon }} size-5 shrink-0"></span>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="relative mt-6 flex items-center gap-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10">
            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 font-display font-bold text-snow">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                <p class="truncate text-xs text-ink-400">{{ $user->email }}</p>
            </div>
            <button type="button" title="Logout" onclick="document.getElementById('logout-form').submit()"
                class="grid size-9 place-items-center rounded-xl text-ink-400 transition hover:bg-rose-500/15 hover:text-rose-400">
                <span class="icon-[tabler--logout] size-5"></span>
            </button>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="lg:pl-72">
        <header
            class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-white/10 bg-black/30 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
            <button type="button" @click="sidebar = true"
                class="grid size-10 place-items-center rounded-xl bg-white/5 text-ink-200 ring-1 ring-white/10 lg:hidden">
                <span class="icon-[tabler--menu-2] size-5"></span>
            </button>

            <div class="min-w-0">
                <p class="text-[11px] font-semibold tracking-wider text-ink-400 uppercase">Admin</p>
                <p class="truncate font-display text-base leading-tight font-bold text-white">@yield('title', 'Dashboard')</p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                @include('layouts.partials.theme-toggle')
                <a href="{{ route('quiz.create') }}" class="btn btn-dark btn-sm hidden sm:inline-flex">
                    <span class="icon-[tabler--plus] size-4"></span> New Quiz
                </a>

                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button type="button" @click="open = !open"
                        class="flex items-center gap-2 rounded-full bg-white/5 py-1 pr-2.5 pl-1 ring-1 ring-white/10 transition hover:ring-white/20">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=e1163a&color=fff&size=64&bold=true"
                            alt="" class="size-8 rounded-full">
                        <span class="icon-[tabler--chevron-down] size-4 text-ink-400 transition" :class="open && 'rotate-180'"></span>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right
                        class="absolute right-0 mt-2 w-56 rounded-2xl bg-ink-950/90 p-2 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl">
                        <div class="border-b border-white/10 px-3 pt-1 pb-3">
                            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs text-ink-400">Administrator</p>
                        </div>
                        <a href="{{ route('admin.profile') }}"
                            class="mt-1 flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-ink-300 hover:bg-white/5 hover:text-white">
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

        <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
            @include('layouts.partials.flash')
            @yield('content')
        </main>
    </div>
</div>
