@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
    <span class="mb-6 grid size-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
        <span class="icon-[tabler--lock-password] size-7"></span>
    </span>
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-white">{{ __('Reset Password') }}</h1>
    <p class="mt-1.5 text-ink-400">নতুন একটি শক্তিশালী পাসওয়ার্ড সেট করুন।</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">{{ __('Email Address') }}</label>
            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                class="input bg-white/5 @error('email') input-error @enderror">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password" class="label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="input bg-white/5 @error('password') input-error @enderror">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password-confirm" class="label">{{ __('Confirm Password') }}</label>
            <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password" class="input bg-white/5">
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-full">{{ __('Reset Password') }}</button>
    </form>
@endsection
