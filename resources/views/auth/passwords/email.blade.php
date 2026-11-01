@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <span class="mb-6 grid size-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
        <span class="icon-[tabler--key] size-7"></span>
    </span>
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-white">{{ __('Reset Password') }}</h1>
    <p class="mt-1.5 text-ink-400">আপনার ইমেইল দিন, আমরা পাসওয়ার্ড রিসেট লিংক পাঠিয়ে দেব।</p>

    @if (session('status'))
        <div class="mt-6 flex items-start gap-2.5 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300 ring-1 ring-emerald-500/25">
            <span class="icon-[tabler--circle-check-filled] mt-0.5 size-5 shrink-0 text-emerald-500"></span>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="label">{{ __('Email Address') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                class="input bg-white/5 @error('email') input-error @enderror">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-full">
            <span class="icon-[tabler--send] size-5"></span> {{ __('Send Password Reset Link') }}
        </button>
    </form>

    <a href="{{ route('login') }}" class="mt-8 flex items-center justify-center gap-1.5 text-sm font-semibold text-ink-400 hover:text-white">
        <span class="icon-[tabler--arrow-left] size-4"></span> লগইনে ফিরে যান
    </a>
@endsection
