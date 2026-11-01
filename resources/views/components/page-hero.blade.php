@props(['title', 'subtitle' => null, 'icon' => null, 'eyebrow' => null])

<section {{ $attributes->class('relative mb-8 animate-rise overflow-hidden rounded-[2rem] glass-panel p-6 text-white sm:p-8') }}>
    <div class="absolute inset-0 bg-grid [mask-image:linear-gradient(to_left,black,transparent)]"></div>
    <div class="absolute -top-24 -right-10 size-72 rounded-full bg-brand-600/50 blur-3xl"></div>
    <div class="absolute -bottom-24 left-1/3 size-56 rounded-full bg-volt-400/10 blur-3xl"></div>

    <div class="relative flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            @if ($icon)
                <span class="hidden size-14 shrink-0 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/15 sm:grid">
                    <span class="{{ $icon }} size-7 text-volt-400"></span>
                </span>
            @endif
            <div>
                @if ($eyebrow)
                    <p class="mb-1 text-[11px] font-bold tracking-[0.2em] text-volt-400 uppercase">{{ $eyebrow }}</p>
                @endif
                <h1 class="font-display text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-ink-300">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endif
    </div>

    {{ $slot }}
</section>
