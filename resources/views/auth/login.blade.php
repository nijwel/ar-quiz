@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div x-data="{ type: '{{ old('login_type', 'email') === 'id' ? 'id' : 'email' }}', show: false }">
        <h1 class="font-display text-3xl font-extrabold tracking-tight text-white">আবার স্বাগতম 👋</h1>
        <p class="mt-1.5 text-ink-400">সঠিক তথ্য দিয়ে লগইন করুন।</p>

        @if (session('error') || $errors->any())
            <div class="mt-6 flex items-start gap-2.5 rounded-2xl bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-300 ring-1 ring-rose-500/25">
                <span class="icon-[tabler--alert-circle-filled] mt-0.5 size-5 shrink-0 text-rose-500"></span>
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        {{-- Login type switch --}}
        <div class="relative mt-8 grid grid-cols-2 rounded-2xl bg-white/5 p-1 ring-1 ring-white/10">
            <span class="absolute inset-y-1 left-1 w-[calc(50%-0.25rem)] rounded-xl bg-white/10 transition-transform duration-300"
                :class="type === 'id' && 'translate-x-full'"></span>
            <button type="button" @click="type = 'email'" :class="type === 'email' ? 'text-white' : 'text-ink-400'"
                class="relative flex items-center justify-center gap-2 py-2.5 text-sm font-semibold transition">
                <span class="icon-[tabler--mail] size-4"></span> Email
            </button>
            <button type="button" @click="type = 'id'" :class="type === 'id' ? 'text-white' : 'text-ink-400'"
                class="relative flex items-center justify-center gap-2 py-2.5 text-sm font-semibold transition">
                <span class="icon-[tabler--id-badge-2] size-4"></span> Student ID
            </button>
        </div>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="login_type" :value="type">

            <div>
                <label for="login" class="label" x-text="type === 'email' ? 'Email Address' : 'Student ID'">Email Address</label>
                <div class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-400"
                        :class="type === 'email' ? 'icon-[tabler--mail]' : 'icon-[tabler--id-badge-2]'"></span>
                    <input id="login" name="login" value="{{ old('login') }}" required autofocus
                        :type="type === 'email' ? 'email' : 'text'"
                        :placeholder="type === 'email' ? 'আপনার ইমেইল লিখুন' : 'আপনার স্টুডেন্ট আইডি লিখুন'"
                        class="input bg-white/5 pl-12 @if (session('error') || $errors->any()) input-error @endif" placeholder="আপনার ইমেইল লিখুন">
                </div>
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="label mb-0">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300">পাসওয়ার্ড ভুলে গেছেন?</a>
                    @endif
                </div>
                <div class="relative">
                    <span class="icon-[tabler--lock] pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-400"></span>
                    <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required
                        class="input bg-white/5 px-12" placeholder="পাসওয়ার্ড লিখুন">
                    <button type="button" @click="show = !show" class="absolute top-1/2 right-3 grid size-8 -translate-y-1/2 place-items-center rounded-lg text-ink-400 hover:text-ink-200">
                        <span :class="show ? 'icon-[tabler--eye-off]' : 'icon-[tabler--eye]'" class="icon-[tabler--eye] size-5"></span>
                    </button>
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-300">
                <input type="checkbox" name="remember" class="size-4 rounded accent-brand-600" {{ old('remember') ? 'checked' : '' }}>
                তথ্য মনে রাখুন
            </label>

            <button type="submit" class="btn btn-primary btn-lg w-full">
                প্রবেশ করুন <span class="icon-[tabler--arrow-right] size-5"></span>
            </button>
        </form>

        <div class="my-8 flex items-center gap-4 text-xs font-semibold tracking-wider text-ink-400 uppercase">
            <span class="h-px flex-1 bg-white/10"></span> অ্যাকাউন্ট নেই? <span class="h-px flex-1 bg-white/10"></span>
        </div>

        <a href="{{ route('register') }}" class="btn btn-ghost btn-lg w-full">
            <span class="icon-[tabler--user-plus] size-5"></span> নতুন অ্যাকাউন্ট তৈরি করুন
        </a>
    </div>
@endsection
