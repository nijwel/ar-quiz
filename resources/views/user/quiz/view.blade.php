@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
            --quiz-secondary: #764ba2;
            --quiz-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* স্টার্ট স্ক্রিন স্টাইল */
        #start-screen {
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .start-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        /* বাকি কুইজ স্টাইল */
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

        .option-container input {
            position: absolute;
            opacity: 0;
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

        .btn-submit,
        .btn-start {
            background: var(--quiz-gradient);
            border: none;
            padding: 12px 40px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
            transition: 0.3s;
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
        }

        #quiz-content {
            position: relative;
        }

        .sticky-timer {
            position: -webkit-sticky;
            position: sticky;
            top: 85px;
            z-index: 1020;
            margin-top: 10px;
            margin-bottom: 30px;
            background: var(--quiz-gradient);
            border-radius: 15px;
            padding: 15px 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .sticky-timer.scrolled {
            padding: 10px 20px;
            border-radius: 0 0 15px 15px;
        }

        .timer-box {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 18px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @media (max-width: 768px) {
            .sticky-timer {
                top: 55px;
                /* মোবাইলে নেভবার ছোট হলে এটিও কমবে */
                padding: 10px 15px;
                margin-left: -5px;
                margin-right: -5px;
                border-radius: 12px;
            }

            .sticky-timer h2 {
                font-size: 1.1rem !important;
                /* টাইটেল ছোট করা */
                margin-bottom: 0 !important;
            }

            .sticky-timer p {
                display: none;
                /* মোবাইল স্ক্রিনে ডেসক্রিপশন লুকানো (জায়গা বাঁচাতে) */
            }

            .timer-box {
                min-width: 70px !important;
                padding: 5px 10px !important;
            }

            #timer {
                font-size: 1.1rem !important;
                /* টাইমার ফন্ট ছোট করা */
            }

            .btn-cancel-text {
                display: none;
                /* শুধু আইকন দেখানোর জন্য বাটন ছোট করা */
            }
        }

        #quiz-content {
            display: none;
        }
    </style>

    <div class="container py-2">

        <div id="start-screen">
            <div class="start-card">
                <i class="bi bi-clock-history text-primary" style="font-size: 3rem;"></i>
                <h2 class="fw-bold mt-3">{{ $quiz->title }}</h2>
                <p class="text-muted">আপনি কি কুইজটি শুরু করতে প্রস্তুত? আপনার কাছে মাত্র {{ $quiz->questions->count() }}
                    মিনিট সময় থাকবে।</p>
                <button id="startBtn" class="btn btn-start btn-lg w-100">কুইজ শুরু করুন</button>
            </div>
        </div>
        <div class="container py-0">
            <div id="quiz-content">
                <div class="quiz-header d-flex justify-content-between align-items-center flex-wrap gap-3 sticky-timer">
                    <div class="text-white">
                        <h4 class="fw-bold mb-0">{{ $quiz->title }}</h4>
                        <p class="mb-0 opacity-75 small d-none d-md-block">সময় শেষ হওয়ার আগে উত্তর জমা দিন।</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="timer-box bg-white text-dark shadow-sm border-0 d-flex flex-column align-items-center">
                            <small class="text-uppercase fw-bold text-muted" style="font-size: 8px;">Progress</small>
                            <div class="fw-bold">
                                <span id="answered-count" class="text-primary">0</span>
                                <span class="text-muted small"> / {{ $quiz->questions->count() }}</span>
                            </div>
                        </div>

                        <div class="timer-box text-white d-flex flex-column align-items-center">
                            <small class="text-uppercase fw-bold" style="font-size: 8px; opacity: 0.8;">Time Left</small>
                            <span id="timer" class="fw-bold">00:00</span>
                        </div>

                        <a href="{{ route('home') }}" class="btn btn-sm btn-light fw-bold rounded px-3"
                            onclick="return confirm('আপনি কি নিশ্চিত?')">Cancel</a>
                    </div>
                </div>

                <form action="{{ route('user.quiz.submit') }}" method="POST" id="quizForm">
                    @csrf
                    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

                    <div class="row">
                        @foreach ($quiz->questions as $key => $question)
                            <div class="col-lg-6">
                                <div class="card question-card">
                                    <div class="card-header bg-white border-0 pt-4 px-4">
                                        <h5 class="question-title">
                                            <span class="text-primary me-2">Q{{ $key + 1 }}.</span>
                                            {{ $question->question }}
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
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ডাইনামিক সময় সেট: প্রশ্নের সংখ্যা * ৬০ সেকেন্ড
            const questionCount = {{ $quiz->questions->count() }};
            const totalTimeInSeconds = questionCount * 60;

            const quizId = "{{ $quiz->id }}";
            const storageKey = `quiz_timer_${quizId}`;

            const startBtn = document.getElementById('startBtn');
            const startScreen = document.getElementById('start-screen');
            const quizContent = document.getElementById('quiz-content');
            const timerDisplay = document.getElementById('timer');
            const quizForm = document.getElementById('quizForm');

            let timeRemaining;
            let timerInterval;

            // চেক করা হচ্ছে আগে থেকে টাইমার চলছে কিনা
            const savedTime = localStorage.getItem(storageKey);
            if (savedTime) {
                initQuiz(parseInt(savedTime));
            }

            // স্টার্ট বাটন ক্লিক করলে কুইজ শুরু হবে
            if (startBtn) {
                startBtn.addEventListener('click', function() {
                    initQuiz(totalTimeInSeconds);
                });
            }

            function initQuiz(startTime) {
                timeRemaining = startTime;
                if (startScreen) startScreen.style.display = 'none';
                if (quizContent) quizContent.style.display = 'block';

                // টাইমার শুরু
                timerInterval = setInterval(updateTimer, 1000);
                updateTimer();
            }

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

                // ১ মিনিটের কম সময় থাকলে টাইমার লাল হয়ে যাবে
                if (timeRemaining < 60) {
                    timerDisplay.parentElement.style.color = "#ff4d4d";
                }
            }

            function handleTimeUp() {
                localStorage.removeItem(storageKey);
                alert("সময় শেষ! আপনার উত্তরগুলো অটোমেটিক সাবমিট হচ্ছে।");
                quizForm.submit();
            }

            quizForm.onsubmit = function() {
                localStorage.removeItem(storageKey);
            };

            window.onbeforeunload = function() {
                if (timeRemaining > 0 && quizContent.style.display !== 'none') {
                    return "আপনি কি কুইজ ছেড়ে চলে যেতে চান?";
                }
            };
        });

        // লাইভ আনসার কাউন্ট লজিক
        const totalQuestions = {{ $quiz->questions->count() }};
        const answeredDisplay = document.getElementById('answered-count');
        const radioButtons = quizForm.querySelectorAll('input[type="radio"]');

        function updateAnsweredCount() {
            // কয়টি আলাদা আলাদা নামের (question_id) রেডিও বাটন সিলেক্ট করা হয়েছে তা চেক করবে
            const answeredQuestions = new Set();
            radioButtons.forEach(radio => {
                if (radio.checked) {
                    answeredQuestions.add(radio.name);
                }
            });
            answeredDisplay.textContent = answeredQuestions.size;

            // সব উত্তর দেওয়া হয়ে গেলে কালার পরিবর্তন (ঐচ্ছিক)
            if (answeredQuestions.size === totalQuestions) {
                answeredDisplay.classList.replace('text-primary', 'text-success');
            }
        }

        // প্রতিটি রেডিও বাটনে ইভেন্ট লিসেনার যোগ করা
        radioButtons.forEach(radio => {
            radio.addEventListener('change', updateAnsweredCount);
        });

        // পেজ রিফ্রেশ হলে বা আগে থেকে উত্তর সেভ থাকলে তা দেখানোর জন্য একবার কল করা
        updateAnsweredCount();
    </script>
@endsection
