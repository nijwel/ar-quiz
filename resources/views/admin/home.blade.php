@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .welcome-banner {
            background: var(--quiz-gradient);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(78, 84, 200, 0.2);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner h2 {
            font-weight: 800;
            letter-spacing: -1px;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .bg-soft-primary {
            background: rgba(78, 84, 200, 0.1);
            color: #4e54c8;
        }

        .bg-soft-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .bg-soft-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .quick-link-card {
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 20px;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
            display: block;
        }

        .quick-link-card:hover {
            background: #f8f9fa;
            border-color: var(--quiz-primary);
            color: var(--quiz-primary);
        }
    </style>

    <div class="container py-4">
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="display-5">Welcome Back, {{ Auth::user()->name }}! 👋</h2>
                    <p class="lead opacity-75 mb-0">আজকের কুইজ স্ট্যাটাস এবং পারফরম্যান্স রিপোর্ট চেক করুন।</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="bi bi-speedometer2" style="font-size: 80px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-primary">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Total Quizzes</h6>
                    <h2 class="fw-extrabold mb-0">{{ $quizCount }}</h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Total Participants</h6>
                    <h2 class="fw-extrabold mb-0">{{ $userCount }}</h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-info">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Active Exams</h6>
                    <h2 class="fw-extrabold mb-0">05</h2>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-4 text-dark">Quick Actions</h4>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('quiz.create') }}" class="quick-link-card text-center">
                    <i class="bi bi-plus-circle fs-3 mb-2 d-block"></i>
                    <span class="fw-bold">Create Quiz</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('quiz.index') }}" class="quick-link-card text-center">
                    <i class="bi bi-list-task fs-3 mb-2 d-block"></i>
                    <span class="fw-bold">Manage Quizzes</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('participant.index') }}" class="quick-link-card text-center">
                    <i class="bi bi-person-badge fs-3 mb-2 d-block"></i>
                    <span class="fw-bold">View Students</span>
                </a>
            </div>
            <div class="col-md-3">
                <a href="#" class="quick-link-card text-center">
                    <i class="bi bi-gear fs-3 mb-2 d-block"></i>
                    <span class="fw-bold">Settings</span>
                </a>
            </div>
        </div>
    </div>
@endsection
