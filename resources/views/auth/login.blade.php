@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #8f94fb;
        }

        .login-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
        }

        .auth-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(45deg, var(--quiz-primary), var(--quiz-secondary));
            padding: 40px;
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

        .btn-login {
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

        .btn-login:hover {
            background: #3f449b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
            color: white;
        }

        .forgot-link {
            color: var(--quiz-primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-5">
                <div class="card auth-card">
                    <div class="auth-header">
                        <h2>{{ __('Quiz Arena') }}</h2>
                        <p class="mb-0">{{ __('স্বাগতম! আপনার মেধা যাচাই করুন') }}</p>
                    </div>

                    <div class="auth-body">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">{{ __('Email Address') }}</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autocomplete="email" autofocus
                                    placeholder="আপনার ইমেইল দিন">
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
                                    autocomplete="current-password" placeholder="পাসওয়ার্ড লিখুন">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="forgot-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-login w-100">
                                {{ __('Login Now') }}
                            </button>

                            <div class="text-center mt-4">
                                <p class="text-muted small">অ্যাকাউন্ট নেই? <a href="{{ route('register') }}"
                                        class="fw-bold text-decoration-none" style="color: var(--quiz-primary);">নতুন তৈরি
                                        করুন</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
