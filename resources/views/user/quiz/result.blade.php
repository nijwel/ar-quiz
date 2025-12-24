@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .result-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 15px;
            text-align: center;
            min-width: 120px;
            backdrop-filter: blur(5px);
        }

        .question-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .answer-item {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 8px;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .correct-answer {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
        }

        .wrong-answer {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }

        .user-selected {
            position: relative;
        }

        .user-selected::after {
            content: "Your Answer";
            position: absolute;
            right: 15px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            opacity: 0.7;
        }
    </style>

    <div class="container py-4">
        <div class="result-header">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
                    <h2 class="fw-bold mb-1">Quiz Analysis: {{ $quiz->title }}</h2>
                    <p class="opacity-75 mb-0">আপনার পারফরম্যান্সের বিস্তারিত নিচে দেখুন।</p>
                    <div class="mt-4">
                        <a href="{{ route('home') }}" class="btn btn-light fw-bold px-4 me-2"
                            style="border-radius: 10px; color: var(--quiz-primary);">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                        <a href="{{ route('user.quiz.index') }}" class="btn btn-outline-light fw-bold px-4"
                            style="border-radius: 10px;">
                            All Exams
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <div class="stat-box">
                            <small class="d-block opacity-75">Score</small>
                            <span class="fs-3 fw-bold">{{ $totalMarks }}</span>
                        </div>
                        <div class="stat-box">
                            <small class="d-block opacity-75">Right</small>
                            <span class="fs-3 fw-bold text-success">{{ $rightAnswers }}</span>
                        </div>
                        <div class="stat-box">
                            <small class="d-block opacity-75">Wrong</small>
                            <span class="fs-3 fw-bold text-danger">{{ $wrongAnswers }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            @foreach ($quiz->questions as $index => $question)
                <div class="col-lg-6">
                    <div class="card question-card">
                        <div
                            class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-start">
                            <h5 class="fw-bold text-dark mb-0" style="max-width: 70%;">
                                <span class="text-primary">Q{{ $index + 1 }}.</span> {{ $question->question }}
                            </h5>
                            @if ($question->is_correct)
                                <span class="badge bg-success status-badge">Correct</span>
                            @else
                                <span class="badge bg-danger status-badge">Incorrect</span>
                            @endif
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="answers-review mt-2">
                                @foreach ($question->answers as $answer)
                                    @php
                                        $isCorrect = $answer->is_correct;
                                        $isUserAnswer = $question->user_answer_id == $answer->id;

                                        $class = '';
                                        if ($isCorrect) {
                                            $class = 'correct-answer';
                                        } elseif ($isUserAnswer && !$isCorrect) {
                                            $class = 'wrong-answer';
                                        }
                                    @endphp

                                    <div
                                        class="answer-item {{ $class }} {{ $isUserAnswer ? 'user-selected' : '' }}">
                                        @if ($isCorrect)
                                            <i class="bi bi-check-circle-fill"></i>
                                        @elseif ($isUserAnswer && !$isCorrect)
                                            <i class="bi bi-x-circle-fill"></i>
                                        @else
                                            <i class="bi bi-circle text-muted"></i>
                                        @endif

                                        <span>{{ $answer->answer }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
