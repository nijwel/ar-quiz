@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .page-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(78, 84, 200, 0.2);
        }

        .quiz-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .quiz-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .quiz-title {
            font-weight: 700;
            color: var(--quiz-primary);
        }

        .stat-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
        }

        .progress-minimal {
            height: 6px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .btn-action {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
            transition: 0.3s;
        }

        .icon-circle {
            width: 45px;
            height: 45px;
            background: rgba(78, 84, 200, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--quiz-primary);
            font-size: 20px;
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-journal-check me-2"></i>Available Quizzes</h2>
                <p class="mb-0 opacity-75">আপনার পছন্দের কুইজটি বেছে নিন এবং নিজেকে যাচাই করুন।</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <small class="opacity-75 d-block">Total Points</small>
                    <span class="fs-4 fw-bold">{{ $totalMarks }}</span>
                </div>
                <a href="{{ route('home') }}" class="btn btn-light fw-bold px-4"
                    style="color: var(--quiz-primary); border-radius: 12px;">
                    <i class="bi bi-house-door me-1"></i> Home
                </a>
            </div>
        </div>

        <div class="row">
            @foreach ($quizzes as $quiz)
                @php
                    $result = $quizResults[$quiz->id];
                @endphp

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card quiz-card h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-circle">
                                    <i class="bi bi-patch-question"></i>
                                </div>
                                @if ($result['attempted'])
                                    <span class="badge bg-success-subtle text-success stat-badge">Completed</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary stat-badge">New</span>
                                @endif
                            </div>

                            <h5 class="quiz-title mb-2">{{ $quiz->title }}</h5>
                            <p class="text-muted small mb-3">{{ Str::limit($quiz->description, 80) }}</p>

                            <div class="quiz-info mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted"><i class="bi bi-list-ol me-1"></i> Questions:
                                        {{ $result['total_questions'] }}</small>
                                    <small class="fw-bold text-primary">{{ $result['marks'] }} Marks</small>
                                </div>

                                @if ($result['attempted'])
                                    <div class="progress progress-minimal">
                                        @php
                                            $percent = ($result['right'] / ($result['total_questions'] ?: 1)) * 100;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <div class="row g-0 mt-2 text-center border-top pt-2">
                                        <div class="col-6 border-end">
                                            <small class="d-block text-muted">Right</small>
                                            <span class="fw-bold text-success">{{ $result['right'] }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="d-block text-muted">Wrong</small>
                                            <span class="fw-bold text-danger">{{ $result['wrong'] }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex gap-2 mt-auto">
                                @if ($result['attempted'])
                                    <button class="btn btn-outline-secondary btn-action w-100 disabled"
                                        style="opacity: 0.5;">
                                        Attempted
                                    </button>
                                    <a href="{{ route('user.quiz.result', $quiz->slug) }}"
                                        class="btn btn-info btn-action w-100 text-white shadow-sm">
                                        Details
                                    </a>
                                @else
                                    <a href="{{ route('user.quiz.show', $quiz->slug) }}"
                                        class="btn btn-primary btn-action w-100 shadow-sm"
                                        style="background: var(--quiz-primary);">
                                        Take Quiz
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
