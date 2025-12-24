@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #8f94fb;
        }

        .welcome-banner {
            background: linear-gradient(45deg, var(--quiz-primary), var(--quiz-secondary));
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(78, 84, 200, 0.2);
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .icon-box {
            width: 50px;
            height: 50px;
            background: rgba(78, 84, 200, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--quiz-primary);
            font-size: 24px;
            margin-bottom: 15px;
        }

        .btn-start-quiz {
            background: #fff;
            color: var(--quiz-primary);
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 25px;
            border: none;
            transition: 0.3s;
        }

        .btn-start-quiz:hover {
            background: #f8f9fa;
            transform: scale(1.05);
        }
    </style>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-11">

                {{-- Success Message --}}
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
                        style="border-radius: 10px;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Welcome Hero Section --}}
                <div class="welcome-banner d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h1 class="fw-bold mb-2">স্বাগতম, {{ Auth::user()->name }}! 👋</h1>
                        <p class="lead mb-4 opacity-75">আজকের কুইজ প্রতিযোগিতার জন্য আপনি কি প্রস্তুত?</p>
                        <a href="/user/quizzes" class="btn btn-start-quiz">কুইজ শুরু করুন</a>
                    </div>
                    <div class="d-none d-lg-block">
                        {{-- আপনি এখানে একটি ইলাস্ট্রেশন ইমেজ দিতে পারেন --}}
                        <img src="https://cdn-icons-png.flaticon.com/512/5692/5692030.png" alt="Quiz Icon"
                            style="width: 150px; filter: brightness(0) invert(1);">
                    </div>
                </div>

                {{-- Stats / Info Row --}}
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card stat-card p-4">
                            <div class="icon-box">🏆</div>
                            <h5 class="text-muted small text-uppercase fw-bold">মোট স্কোর</h5>
                            <h3 class="fw-bold">{{ $totalMarks }}</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card p-4">
                            <div class="icon-box">📝</div>
                            <h5 class="text-muted small text-uppercase fw-bold">সম্পন্ন কুইজ</h5>
                            <h3 class="fw-bold">{{ $quiz_attempted }} টি</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card p-4">
                            <div class="icon-box">⚡</div>
                            <h5 class="text-muted small text-uppercase fw-bold">র‍্যাঙ্কিং</h5>
                            <h3 class="fw-bold">#{{ $ranking }}</h3>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
