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

        /* Navbar Styling (Desktop) */
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

        .nav-link:hover,
        .nav-item.active .nav-link {
            color: var(--primary-color) !important;
            background: rgba(78, 84, 200, 0.05);
        }

        /* প্রফেশনাল মোবাইল বটম নেভিগেশন */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.08);
            z-index: 1050;
            padding: 0px 10px 0px 10px;
            border-radius: 25px 25px 0 0;
            display: none;
        }

        @media (max-width: 768px) {
            .mobile-bottom-nav {
                display: flex;
                justify-content: space-around;
                align-items: center;
            }

            .mobile-nav-item {
                text-decoration: none;
                color: #a0a0a0;
                display: flex;
                flex-direction: column;
                align-items: center;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                flex: 1;
            }

            /* আইকন স্টাইল */
            .mobile-nav-item i {
                font-size: 22px;
                transition: all 0.3s;
                z-index: 2;
            }

            .mobile-nav-item span {
                font-size: 11px;
                font-weight: 600;
                margin-top: 4px;
                transition: all 0.3s;
            }

            /* অ্যাক্টিভ ট্যাবের বিশেষ ডিজাইন */
            .mobile-nav-item.active {
                color: var(--primary-color);
                transform: translateY(-12px);
                /* একটু উপরে উঠে আসবে */
            }

            .mobile-nav-item.active i {
                background: var(--primary-color);
                color: white;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                box-shadow: 0 10px 20px rgba(78, 84, 200, 0.3);
                border: 5px solid #fff;
                /* সাদা বর্ডার যা ফ্লোটিং লুক দিবে */
            }

            /* অ্যাক্টিভ অবস্থায় টেক্সট লুকানো (প্রফেশনাল অ্যাপের মতো) */
            .mobile-nav-item.active span {
                transform: translateY(10px);
                opacity: 0;
            }

            /* কন্টেন্ট গ্যাপ */
            main {
                padding-bottom: 100px !important;
            }
        }

        /* Dropdown & Other Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px;
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

        ::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
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

                <div class="d-md-none ms-auto">
                    @auth
                        <a class="text-danger fs-4 me-2" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    @endauth
                </div>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto ms-lg-4">
                        @auth
                            @if (Auth::user()->type == 'admin')
                                <li class="nav-item {{ request()->is('admin/quiz*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('quiz.index') }}"><i
                                            class="bi bi-journal-text me-1"></i> Quizzes</a>
                                </li>
                                <li class="nav-item {{ request()->is('admin/participant*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('participant.index') }}"><i
                                            class="bi bi-people me-1"></i> Participants</a>
                                </li>
                            @else
                                <li class="nav-item {{ request()->is('user/quiz*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('user.quiz.index') }}"><i
                                            class="bi bi-pencil-square me-1"></i> New Exams</a>
                                </li>
                                <li class="nav-item {{ request()->is('user/my-quiz*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('user.my.quiz') }}"><i
                                            class="bi bi-pencil-square me-1"></i> My Exams</a>
                                </li>
                                <li class="nav-item {{ request()->is('user/quiz/leaderboard*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('user.quiz.leaderboard') }}"><i
                                            class="bi bi-trophy me-1"></i> Leaderboard</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item"><a class="nav-link"
                                        href="{{ route('login') }}">{{ __('Login') }}</a></li>
                            @endif
                            @if (Route::has('register'))
                                <li class="nav-item ms-lg-2"><a class="btn btn-primary px-4 shadow-sm"
                                        style="border-radius: 8px; background: var(--primary-color);"
                                        href="{{ route('register') }}">{{ __('Register') }}</a></li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center"
                                    href="#" role="button" data-bs-toggle="dropdown">
                                    <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                                    <span>{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end animated fadeIn">
                                    <a class="dropdown-item"
                                        href="{{ Auth::user()->type == 'admin' ? route('admin.profile') : route('user.profile') }}">
                                        <i class="bi bi-person me-2"></i> Profile
                                    </a>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @auth
            <div class="mobile-bottom-nav">
                @if (Auth::user()->type == 'admin')
                    <a href="{{ url('/admin/home') }}"
                        class="mobile-nav-item {{ request()->is('admin/home') ? 'active' : '' }}">
                        <i class="bi bi-house-door{{ request()->is('admin/home') ? '-fill' : '' }}"></i>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('quiz.index') }}"
                        class="mobile-nav-item {{ request()->is('admin/quiz*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i>
                        <span>Quizzes</span>
                    </a>
                    <a href="{{ route('participant.index') }}"
                        class="mobile-nav-item {{ request()->is('admin/participant*') ? 'active' : '' }}">
                        <i class="bi bi-people{{ request()->is('admin/participant*') ? '-fill' : '' }}"></i>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.profile') }}"
                        class="mobile-nav-item {{ request()->is('admin/profile*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i>
                        <span>Profile</span>
                    </a>
                @else
                    <a href="{{ url('user/home') }}"
                        class="mobile-nav-item {{ request()->is('user/home') ? 'active' : '' }}">
                        <i class="bi bi-house-door{{ request()->is('user/home') ? '-fill' : '' }}"></i>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('user.quiz.index') }}"
                        class="mobile-nav-item {{ request()->is('user/quiz*') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square"></i>
                        <span>Exams</span>
                    </a>
                    <a href="{{ route('user.quiz.leaderboard') }}"
                        class="mobile-nav-item {{ request()->is('*leader/board*') ? 'active' : '' }}">
                        <i class="bi bi-trophy{{ request()->is('*leader/board*') ? '-fill' : '' }}"></i>
                        <span>Ranks</span>
                    </a>
                    <a href="{{ route('user.profile') }}"
                        class="mobile-nav-item {{ request()->is('user/profile*') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i>
                        <span>Profile</span>
                    </a>
                @endif
            </div>
        @endauth

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
