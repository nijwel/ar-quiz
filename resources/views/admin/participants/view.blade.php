@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Profile Header */
        .profile-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(78, 84, 200, 0.2);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
        }

        /* Quiz Card Styling */
        .result-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.1);
        }

        .card-quiz-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }

        .stat-value {
            font-weight: 700;
        }

        .accuracy-progress {
            height: 10px;
            border-radius: 10px;
            background-color: #f0f0f0;
            margin: 15px 0 5px 0;
        }

        .badge-soft-primary {
            background-color: rgba(78, 84, 200, 0.1);
            color: var(--quiz-primary);
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 700;
        }
    </style>

    <div class="container py-4">
        <div class="profile-header">
            <div class="d-flex align-items-center flex-wrap gap-4">
                <div class="profile-avatar">
                    {{ strtoupper(substr($participant->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <h1 class="fw-bold mb-1">{{ $participant->name }}</h1>
                    <p class="mb-0 opacity-75"><i class="bi bi-envelope me-2"></i>{{ $participant->email }}</p>
                    <div class="mt-3">
                        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-trophy-fill me-1"></i> Global Participant
                        </span>
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ url()->previous() }}" class="btn btn-light fw-bold rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-journal-check me-2 text-primary"></i>Exam History & Performance
        </h4>

        <div class="row">
            @foreach ($quizzes as $quiz)
                @php
                    $userAnswers = $quiz->userAnswers;
                    $totalQ = $quiz->questions_count;
                    $attempted = $userAnswers->count();
                    $right = $userAnswers->where('status', 'correct')->count();
                    $wrong = $attempted - $right;
                    $score = $right;
                    $percentage = $totalQ > 0 ? ($score / $totalQ) * 100 : 0;
                @endphp

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card result-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start card-quiz-title">
                                <h5 class="mb-0 fw-bold">{{ $quiz->title }}</h5>
                                <span class="badge-soft-primary">{{ round($percentage) }}%</span>
                            </div>

                            <div class="quiz-stats mt-3">
                                <div class="stat-row">
                                    <span class="stat-label">Total Questions</span>
                                    <span class="stat-value text-dark">{{ $totalQ }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Attempted</span>
                                    <span class="stat-value text-primary">{{ $attempted }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label text-success">Correct Answers</span>
                                    <span class="stat-value text-success">{{ $right }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label text-danger">Wrong Answers</span>
                                    <span class="stat-value text-danger">{{ $wrong }}</span>
                                </div>
                            </div>

                            <div class="progress accuracy-progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%"
                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted fw-bold" style="font-size: 11px;">PERFORMANCE ACCURACY</small>

                            <div class="mt-4 pt-3 border-top text-center">
                                <span class="fs-4 fw-bold text-dark">{{ $score }}</span>
                                <span class="text-muted fw-bold"> / {{ $totalQ }} Marks</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
