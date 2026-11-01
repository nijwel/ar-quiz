@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <h1 class="font-display text-3xl font-extrabold tracking-tight text-white">Create Account ✨</h1>
    <p class="mt-1.5 text-ink-400">আমাদের কুইজ কমিউনিটিতে যোগ দিন।</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        @foreach ([
            ['name', 'text', 'Full Name', 'আপনার পুরো নাম লিখুন', 'name', 'icon-[tabler--user]'],
            ['email', 'email', 'Email Address', 'ইমেইল ঠিকানা দিন', 'email', 'icon-[tabler--mail]'],
            ['password', 'password', 'Password', 'নতুন পাসওয়ার্ড দিন', 'new-password', 'icon-[tabler--lock]'],
            ['password_confirmation', 'password', 'Confirm Password', 'আবার পাসওয়ার্ডটি লিখুন', 'new-password', 'icon-[tabler--lock-check]'],
        ] as [$field, $type, $label, $placeholder, $autocomplete, $icon])
            <div>
                <label for="{{ $field }}" class="label">{{ $label }}</label>
                <div class="relative">
                    <span class="{{ $icon }} pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-ink-400"></span>
                    <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}"
                        value="{{ $type === 'password' ? '' : old($field) }}" required autocomplete="{{ $autocomplete }}"
                        placeholder="{{ $placeholder }}" @if ($loop->first) autofocus @endif
                        class="input bg-white/5 pl-12 @error($field) input-error @enderror">
                </div>
                @error($field)
                    <p class="field-error"><span class="icon-[tabler--alert-circle] size-3.5"></span> {{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Register Now <span class="icon-[tabler--arrow-right] size-5"></span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-ink-400">
        ইতিমধ্যে অ্যাকাউন্ট আছে?
        <a href="{{ route('login') }}" class="font-bold text-brand-400 hover:text-brand-300">লগইন করুন</a>
    </p>
@endsection
