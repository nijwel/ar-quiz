@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .quiz-header {
            background: var(--quiz-gradient);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .question-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            transition: 0.3s;
        }

        .question-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .question-title {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        /* Custom Radio Styling */
        .option-container {
            display: block;
            position: relative;
            padding: 12px 15px 12px 45px;
            margin-bottom: 12px;
            cursor: pointer;
            background: #f8f9fa;
            border: 2px solid #eee;
            border-radius: 10px;
            transition: 0.2s;
            font-size: 0.95rem;
        }

        .option-container:hover {
            background: #f1f3f9;
            border-color: #d1d9e6;
        }

        .option-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #ccc;
            border-radius: 50%;
        }

        .option-container input:checked~.checkmark {
            background-color: var(--quiz-primary);
            border-color: var(--quiz-primary);
        }

        .option-container input:checked~.checkmark:after {
            display: block;
        }

        .option-container .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            top: 5px;
            left: 5px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: white;
        }

        .option-container input:checked+span {
            font-weight: 600;
            color: var(--quiz-primary);
        }

        .option-container input:checked {
            border-color: var(--quiz-primary);
        }

        .btn-submit {
            background: var(--quiz-gradient);
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.4);
            color: white;
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            min-width: 150px;
            text-align: center;
        }

        #timer {
            font-size: 1.5rem;
            font-weight: 800;
            font-family: 'Courier New', Courier, monospace;
        }

        .sticky-timer {
            position: sticky;
            top: 20px;
            z-index: 1000;
        }
    </style>

    <div class="container py-4">
        <div class="quiz-header d-flex justify-content-between align-items-center flex-wrap gap-3 sticky-timer">
            <div>
                <h2 class="fw-bold mb-1">{{ $quiz->title }}</h2>
                <p class="mb-0 opacity-75">সময় শেষ হওয়ার আগে উত্তর জমা দিন।</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="timer-box">
                    <small class="d-block text-uppercase fw-bold" style="font-size: 10px;">Time Remaining</small>
                    <span id="timer">00:00</span>
                </div>
                <a href="{{ route('home') }}" class="btn btn-light fw-bold"
                    onclick="return confirm('আপনি কি নিশ্চিত? প্রগতি হারিয়ে যাবে।')">
                    Cancel
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert"
                style="border-radius: 15px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('user.quiz.submit') }}" method="POST" id="quizForm">
            @csrf
            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

            <div class="row">
                @foreach ($quiz->questions as $key => $question)
                    <div class="col-lg-6">
                        <div class="card question-card">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h5 class="question-title">
                                    <span class="text-primary me-2">Q{{ $key + 1 }}.</span> {{ $question->question }}
                                </h5>
                                <input type="hidden" name="question_id[]" value="{{ $question->id }}">
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="options-list">
                                    @foreach ($question->answers as $answer)
                                        <label class="option-container">
                                            <input type="radio" name="answers[{{ $question->id }}]"
                                                value="{{ $answer->id }}">
                                            <span>{{ $answer->answer }}</span>
                                            <span class="checkmark"></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-4 mb-5">
                <button class="btn btn-submit btn-lg" type="submit">
                    <i class="bi bi-send-fill me-2"></i> Submit My Answers
                </button>
            </div>
        </form>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ১০ মিনিট বা কুইজের সময় অনুযায়ী সেকেন্ড
        const totalTimeInSeconds = 10;
        const quizId = "{{ $quiz->id }}";
        const storageKey = `quiz_timer_${quizId}`;

        let timeRemaining;
        const savedTime = localStorage.getItem(storageKey);

        if (savedTime) {
            timeRemaining = parseInt(savedTime);
        } else {
            timeRemaining = totalTimeInSeconds;
            localStorage.setItem(storageKey, timeRemaining);
        }

        const timerDisplay = document.getElementById('timer');
        const quizForm = document.getElementById('quizForm');

        function updateTimer() {
            let minutes = Math.floor(timeRemaining / 60);
            let seconds = timeRemaining % 60;

            timerDisplay.textContent =
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                handleTimeUp();
            } else {
                timeRemaining--;
                localStorage.setItem(storageKey, timeRemaining);
            }

            if (timeRemaining < 60) {
                timerDisplay.parentElement.style.color = "#ff4d4d";
            }
        }

        function handleTimeUp() {
            localStorage.removeItem(storageKey);
            alert("সময় শেষ! আপনার উত্তরগুলো অটোমেটিক সাবমিট হচ্ছে।");
            quizForm.submit();
        }

        const timerInterval = setInterval(updateTimer, 1000);

        quizForm.onsubmit = function() {
            localStorage.removeItem(storageKey);
        };

        window.onbeforeunload = function() {
            if (timeRemaining > 0) {
                // কিছু ব্রাউজারে কাস্টম মেসেজ দেখায় না, কিন্তু ওয়ার্নিং দেয়
                return "আপনি কি কুইজ ছেড়ে চলে যেতে চান?";
            }
        };
    });
</script>
