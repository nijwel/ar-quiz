<div class="flex min-h-full">
    {{-- Brand panel --}}
    <aside class="relative hidden w-[46%] overflow-hidden border-r border-white/10 bg-black/30 backdrop-blur-xl p-12 text-white lg:flex lg:flex-col">
        <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_at_top_left,black,transparent_70%)]"></div>
        <div class="absolute -top-32 -right-32 size-[28rem] rounded-full bg-brand-600/40 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-20 size-96 rounded-full bg-volt-400/15 blur-3xl"></div>

        <a href="{{ route('login') }}" class="relative flex items-center gap-3">
            <x-brand-mark size="size-11" icon-size="size-6" class="rounded-2xl" tone="bg-volt-400" />
            <span class="font-display text-xl font-bold">{{ config('app.name', 'QuizArena') }}</span>
        </a>

        <div class="relative my-auto max-w-md">
            <span class="badge mb-6 bg-white/10 text-volt-300 ring-1 ring-white/15">
                <span class="size-1.5 animate-pulse rounded-full bg-volt-400"></span> Live exams running
            </span>
            <h1 class="font-display text-5xl leading-[1.05] font-extrabold tracking-tight">
                শেখো, খেলো,<br>
                <span class="bg-gradient-to-r from-volt-300 to-brand-300 bg-clip-text text-transparent">এগিয়ে যাও।</span>
            </h1>
            <p class="mt-5 text-base leading-relaxed text-ink-300">
                টাইমড কুইজ দাও, সাথে সাথে রেজাল্ট দেখো আর লিডারবোর্ডে নিজের জায়গা করে নাও।
            </p>

            {{-- Floating preview card --}}
            <div class="mt-10 animate-float rounded-3xl bg-white/[0.06] p-5 ring-1 ring-white/10 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wider text-ink-400 uppercase">Question 04 / 10</span>
                    <span class="badge bg-volt-400 text-snow"><span class="icon-[tabler--clock] size-3.5"></span> 02:45</span>
                </div>
                <p class="mb-4 font-semibold">বাংলাদেশের জাতীয় ফুল কোনটি?</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <span class="rounded-xl bg-white/5 px-3 py-2 text-ink-300 ring-1 ring-white/10">গোলাপ</span>
                    <span class="flex items-center justify-between rounded-xl bg-volt-400/15 px-3 py-2 text-volt-300 ring-1 ring-volt-400/50">
                        শাপলা <span class="icon-[tabler--circle-check-filled] size-4"></span>
                    </span>
                    <span class="rounded-xl bg-white/5 px-3 py-2 text-ink-300 ring-1 ring-white/10">জবা</span>
                    <span class="rounded-xl bg-white/5 px-3 py-2 text-ink-300 ring-1 ring-white/10">কদম</span>
                </div>
            </div>
        </div>

        <p class="relative text-xs text-ink-400">&copy; {{ date('Y') }} {{ config('app.name', 'QuizArena') }}</p>
    </aside>

    {{-- Form panel --}}
    <main class="relative flex flex-1 items-center justify-center px-5 py-10 sm:px-10">
        <div class="pointer-events-none absolute inset-0 bg-dots [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
        <div class="absolute top-5 right-5 z-10">
            @include('layouts.partials.theme-toggle')
        </div>
        <div class="relative w-full max-w-md animate-rise">
            <a href="{{ route('login') }}" class="mb-10 flex items-center gap-2.5 lg:hidden">
                <x-brand-mark />
                <span class="font-display text-lg font-bold text-white">{{ config('app.name', 'QuizArena') }}</span>
            </a>
            @yield('content')
        </div>
    </main>
</div>
