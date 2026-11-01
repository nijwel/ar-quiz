@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
    <div class="card mx-auto max-w-md p-8 text-center">
        <span class="mx-auto mb-6 grid size-16 place-items-center rounded-2xl bg-brand-500/10 text-brand-400">
            <span class="icon-[tabler--mail-check] size-8"></span>
        </span>
        <h1 class="font-display text-2xl font-extrabold text-white">{{ __('Verify Your Email Address') }}</h1>

        @if (session('resent'))
            <div class="mt-5 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300 ring-1 ring-emerald-500/25">
                {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
        @endif

        <p class="mt-3 text-sm text-ink-400">
            {{ __('Before proceeding, please check your email for a verification link.') }}
            {{ __('If you did not receive the email') }},
        </p>
        <form method="POST" action="{{ route('verification.resend') }}" class="mt-6">
            @csrf
            <button type="submit" class="btn btn-primary">{{ __('click here to request another') }}</button>
        </form>
    </div>
@endsection
