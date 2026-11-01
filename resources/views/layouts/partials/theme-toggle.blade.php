<button type="button" x-data="{ light: document.documentElement.classList.contains('light') }"
    @click="light = !light; document.documentElement.classList.toggle('light', light); localStorage.setItem('theme', light ? 'light' : 'dark')"
    :title="light ? 'Dark mode' : 'Light mode'" aria-label="Toggle theme"
    class="relative grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl bg-white/5 text-ink-300 ring-1 ring-white/10 backdrop-blur transition hover:text-white {{ $class ?? '' }}">
    <span class="icon-[tabler--sun] absolute size-5 text-amber-400 transition duration-500"
        :class="light ? 'rotate-0 scale-100 opacity-100' : 'rotate-90 scale-0 opacity-0'"></span>
    <span class="icon-[tabler--moon-stars] absolute size-5 transition duration-500"
        :class="light ? '-rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'"></span>
</button>
