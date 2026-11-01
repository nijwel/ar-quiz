@php
    $homeUrl = auth()->check() && auth()->user()->type == 'admin' ? url('/admin/home') : (auth()->check() ? route('home') : route('login'));
@endphp

<div class="grid min-h-[60vh] place-items-center py-10 text-center">
    <div class="max-w-md animate-rise">
        <p class="relative font-display text-[8rem] leading-none font-extrabold tracking-tighter text-white sm:text-[10rem]">
            {{ $code }}
            <span class="{{ $icon }} absolute -top-2 -right-6 size-14 rotate-12 text-volt-500"></span>
        </p>
        <h1 class="mt-4 font-display text-2xl font-bold text-white">{{ $title }}</h1>
        <p class="mt-2 text-ink-400">{{ $message }}</p>
        <div class="mt-8 flex justify-center gap-2">
            @if ($code == 500)
                <a href="javascript:location.reload()" class="btn btn-ghost">
                    <span class="icon-[tabler--refresh] size-4"></span> রিফ্রেশ দিন
                </a>
            @endif
            <a href="{{ $homeUrl }}" class="btn btn-dark">
                <span class="icon-[tabler--smart-home] size-4"></span> হোম পেজে ফিরে যান
            </a>
        </div>
    </div>
</div>
