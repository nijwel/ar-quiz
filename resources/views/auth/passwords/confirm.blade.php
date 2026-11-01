@extends('layouts.app')

@section('title', 'Confirm Password')

@section('content')
    <span class="mb-6 grid size-14 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
        <span class="icon-[tabler--shield-lock] size-7"></span>
    </span>
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-white">{{ __('Confirm Password') }}</h1>
    <p class="mt-1.5 text-ink-400">{{ __('Please confirm your password before continuing.') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="password" class="label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="input bg-white/5 @error('password') input-error @enderror">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-full">{{ __('Confirm Password') }}</button>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="block text-center text-sm font-semibold text-brand-400 hover:text-brand-300">
                {{ __('Forgot Your Password?') }}
            </a>
        @endif
    </form>
@endsection
