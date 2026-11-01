@php
    $flashes = [
        'success' => ['bg-emerald-500/10 text-emerald-300 ring-emerald-500/25', 'icon-[tabler--circle-check-filled] text-emerald-500'],
        'status' => ['bg-emerald-500/10 text-emerald-300 ring-emerald-500/25', 'icon-[tabler--circle-check-filled] text-emerald-500'],
        'warning' => ['bg-amber-500/10 text-amber-300 ring-amber-500/25', 'icon-[tabler--alert-triangle-filled] text-amber-500'],
        'error' => ['bg-rose-500/10 text-rose-300 ring-rose-500/25', 'icon-[tabler--alert-circle-filled] text-rose-500'],
    ];
    $hasFlash = $errors->any() || collect(array_keys($flashes))->contains(fn($key) => session()->has($key));
@endphp

@if ($hasFlash)
<div class="mb-6 space-y-3">
    @foreach ($flashes as $key => [$tone, $icon])
        @if (session($key))
            <div x-data="{ open: true }" x-show="open" x-transition.opacity.duration.300ms
                class="flex animate-rise items-start gap-3 rounded-2xl px-4 py-3 text-sm font-medium ring-1 {{ $tone }}">
                <span class="{{ $icon }} mt-0.5 size-5 shrink-0"></span>
                <div class="flex-1">{{ session($key) }}</div>
                <button type="button" @click="open = false" class="opacity-50 transition hover:opacity-100"
                    aria-label="Close">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div x-data="{ open: true }" x-show="open"
            class="flex animate-rise items-start gap-3 rounded-2xl bg-rose-500/10 px-4 py-3 text-sm text-rose-300 ring-1 ring-rose-500/25">
            <span class="icon-[tabler--alert-circle-filled] mt-0.5 size-5 shrink-0 text-rose-500"></span>
            <ul class="flex-1 list-inside space-y-0.5 font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" @click="open = false" class="opacity-50 transition hover:opacity-100"
                aria-label="Close">
                <span class="icon-[tabler--x] size-4"></span>
            </button>
        </div>
    @endif
</div>
@endif
