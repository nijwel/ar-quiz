@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #8f94fb;
            --quiz-gradient: linear-gradient(45deg, #4e54c8, #8f94fb);
        }

        .login-container {
            min-height: 85vh;
            display: flex;
            align-items: center;
        }

        .auth-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: #fff;
        }

        .auth-header {
            background: var(--quiz-gradient);
            padding: 30px;
            color: white;
            text-align: center;
        }

        .auth-header h2 {
            font-weight: 800;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .auth-body {
            padding: 30px 40px 40px;
        }

        /* Tab Styling */
        .login-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 12px;
        }

        .login-tab-btn {
            flex: 1;
            border: none;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            color: #666;
            transition: 0.3s;
        }

        .login-tab-btn.active {
            background: white;
            color: var(--quiz-primary);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        /* Input Styling */
        .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            border: 1.5px solid #eee;
            transition: 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 15px rgba(78, 84, 200, 0.15);
            border-color: var(--quiz-primary);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 1.5px solid #eee;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--quiz-primary);
        }

        .form-control.with-icon {
            border-radius: 0 12px 12px 0;
        }

        .btn-login {
            background: var(--quiz-gradient);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            color: white;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.4);
            color: white;
        }

        .btn-create-account {
            /* background: var(--quiz-gradient); */
            border: 1px solid var(--quiz-primary);
            background: transparent;
            border-radius: 12px;
            padding: 7px;
            font-weight: 400;
            color: var(--quiz-primary);
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
        }

        .btn-create-account:hover {
            transform: translateY(-3px);
            background: var(--quiz-gradient);
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.4);
            color: white;
        }

        .forgot-link {
            color: var(--quiz-primary);
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>

    <div class="container login-container">
        <div class="row justify-content-center w-100">
            <div class="col-md-5">
                <div class="card auth-card">
                    <div class="auth-header">
                        <h2>{{ __('Quiz Arena') }}</h2>
                        <p class="mb-0 opacity-75 small">{{ __('সঠিক তথ্য দিয়ে লগইন করুন') }}</p>
                    </div>

                    <div class="auth-body">
                        <div class="login-tabs">
                            <button type="button" class="login-tab-btn active" onclick="switchLogin('email')">
                                <i class="bi bi-envelope me-1"></i> Email
                            </button>
                            <button type="button" class="login-tab-btn" onclick="switchLogin('id')">
                                <i class="bi bi-person-badge me-1"></i> Student ID
                            </button>
                        </div>

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf
                            <input type="hidden" name="login_type" id="login_type" value="email">

                            <div class="mb-4">
                                <label id="inputLabel" for="login"
                                    class="form-label fw-bold">{{ __('Email Address') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i id="inputIcon" class="bi bi-envelope"></i></span>
                                    <input id="login" type="email"
                                        class="form-control with-icon @error('email') is-invalid @enderror @error('student_id') is-invalid @enderror"
                                        name="login" value="{{ old('login') }}" required autofocus
                                        placeholder="আপনার ইমেইল লিখুন">
                                </div>
                                @if (session('error'))
                                    <span
                                        class="text-danger small mt-1 d-block"><strong>{{ session('error') }}</strong></span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">{{ __('Password') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input id="password" type="password"
                                        class="form-control with-icon @error('password') is-invalid @enderror"
                                        name="password" required placeholder="পাসওয়ার্ড লিখুন">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="remember">{{ __('তথ্য মনে রাখুন') }}</label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="forgot-link text-decoration-none" href="{{ route('password.request') }}">
                                        {{ __('পাসওয়ার্ড ভুলে গেছেন?') }}
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-login w-100 mb-3">
                                {{ __('প্রবেশ করুন') }}
                            </button>

                            <div class="text-center">
                                <p class="text-muted small mb-0">অ্যাকাউন্ট নেই?</p>
                                <a href="{{ route('register') }}" class="btn btn-create-account w-100 mt-2">
                                    <i class="bi bi-person-plus me-1"></i> নতুন অ্যাকাউন্ট তৈরি করুন
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchLogin(type) {
            const label = document.getElementById('inputLabel');
            const input = document.getElementById('login');
            const icon = document.getElementById('inputIcon');
            const typeField = document.getElementById('login_type');
            const buttons = document.querySelectorAll('.login-tab-btn');
            const inputType = type === 'email' ? 'email' : 'text';

            // Update Tab Buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            if (type === 'email') {
                label.innerText = 'Email Address';
                input.placeholder = 'আপনার ইমেইল লিখুন';
                icon.className = 'bi bi-envelope';
                typeField.value = 'email';
                input.type = inputType;
            } else {
                label.innerText = 'Student ID';
                input.placeholder = 'আপনার স্টুডেন্ট আইডি লিখুন';
                icon.className = 'bi bi-person-badge';
                typeField.value = 'id';
                input.type = inputType;
            }
        }
    </script>
@endsection
