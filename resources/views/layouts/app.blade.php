<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'QuizArena') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">


    <style>
        :root {
            --primary-color: #4e54c8;
            --secondary-color: #8f94fb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Styling */
        .navbar {
            background: white !important;
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            font-weight: 500;
            color: #555 !important;
            padding: 8px 15px !important;
            border-radius: 8px;
            transition: 0.3s;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(78, 84, 200, 0.05);
        }

        .nav-item.active .nav-link {
            color: var(--primary-color) !important;
            background: rgba(78, 84, 200, 0.1);
        }

        /* User Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .dropdown-item:hover {
            background-color: rgba(78, 84, 200, 0.05);
            color: var(--primary-color);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            font-size: 0.8rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* মোবাইল ডিভাইসের জন্য বিশেষ স্টাইল */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .welcome-banner {
                padding: 20px;
                text-align: center;
            }

            .welcome-banner img {
                display: none;
                /* মোবাইলে ইমেজ হাইড থাকবে জায়গা বাঁচাতে */
            }

            .auth-card {
                margin: 10px;
            }

            .stat-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand"
                    href="{{ auth()->check() && auth()->user()->type == 'admin' ? url('/admin/home') : url('user/home') }}">
                    <i class="bi bi-lightning-fill"></i> {{ config('app.name', 'QuizArena') }}
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto ms-lg-4">
                        @auth
                            @if (Auth::user()->type == 'admin')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/quiz*') ? 'active' : '' }}"
                                        href="{{ route('quiz.index') }}">
                                        <i class="bi bi-journal-text me-1"></i> Quizzes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('admin/participant*') ? 'active' : '' }}"
                                        href="{{ route('participant.index') }}">
                                        <i class="bi bi-people me-1"></i> Participants
                                    </a>
                                </li>
                            @elseif(Auth::user()->type == 'user')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('user/quiz*') ? 'active' : '' }}"
                                        href="{{ route('user.quiz.index') }}">
                                        <i class="bi bi-pencil-square me-1"></i> My Exams
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('user.quiz.leaderboard') }}">
                                        <i class="bi bi-trophy me-1"></i> Leaderboard
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item ms-lg-2">
                                    <a class="btn btn-primary px-4 shadow-sm"
                                        style="border-radius: 8px; background: var(--primary-color);"
                                        href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center"
                                    href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" v-pre>
                                    <div class="user-avatar">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <span>{{ Auth::user()->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end animated fadeIn"
                                    aria-labelledby="navbarDropdown">
                                    <div class="px-3 py-2 border-bottom mb-2">
                                        <small class="text-muted d-block">Signed in as</small>
                                        <span
                                            class="fw-bold">{{ Auth::user()->type == 'admin' ? 'Administrator' : 'Student' }}</span>
                                        <small>#{{ auth()->user()->student_id }}</small>
                                    </div>

                                    <a class="dropdown-item"
                                        href="{{ Auth::user()->type == 'admin' ? route('admin.profile') : route('user.profile') }}">
                                        <i class="bi bi-person me-2"></i> Profile
                                    </a>

                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
