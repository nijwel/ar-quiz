@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #8f94fb;
        }

        .auth-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .auth-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(45deg, var(--quiz-primary), var(--quiz-secondary));
            padding: 30px;
            color: white;
            text-align: center;
        }

        .auth-header h2 {
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .auth-body {
            padding: 40px;
            background: #fff;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e1e1e1;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(78, 84, 200, 0.1);
            border-color: var(--quiz-primary);
        }

        .btn-register {
            background: var(--quiz-primary);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            color: white;
        }

        .btn-register:hover {
            background: #3f449b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
            color: white;
        }

        /* মোবাইল ডিভাইসের জন্য অ্যাডজাস্টমেন্ট */
        @media (max-width: 576px) {
            .auth-body {
                padding: 25px;
            }

            .auth-header {
                padding: 20px;
            }
        }
    </style>

    <div class="container auth-container">
        <div class="row justify-content-center w-100 m-0">
            <div class="col-md-6 col-lg-5">
                <div class="card auth-card">
                    <div class="auth-header">
                        <h2>{{ __('Create Account') }}</h2>
                        <p class="mb-0">{{ __('আমাদের কুইজ কমিউনিটিতে যোগ দিন') }}</p>
                    </div>

                    <div class="auth-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">{{ __('Full Name') }}</label>
                                <input id="name" type="text"
                                    class="form-control @error('name') is-invalid @enderror" name="name"
                                    value="{{ old('name') }}" required autocomplete="name" autofocus
                                    placeholder="আপনার পুরো নাম লিখুন">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">{{ __('Email Address') }}</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autocomplete="email"
                                    placeholder="ইমেইল ঠিকানা দিন">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="new-password" placeholder="নতুন পাসওয়ার্ড দিন">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm"
                                    class="form-label fw-bold">{{ __('Confirm Password') }}</label>
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="আবার পাসওয়ার্ডটি লিখুন">
                            </div>

                            <button type="submit" class="btn btn-register w-100">
                                {{ __('Register Now') }}
                            </button>

                            <div class="text-center mt-4">
                                <p class="text-muted small">ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login') }}"
                                        class="fw-bold text-decoration-none" style="color: var(--quiz-primary);">লগইন
                                        করুন</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
