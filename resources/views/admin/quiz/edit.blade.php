@extends('layouts.app')

@section('content')
    <style>
        :root {
            --quiz-primary: #4e54c8;
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

        .form-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .question-block {
            border: 2px solid #f1f3f9 !important;
            border-radius: 15px;
            transition: 0.3s;
            background: #fff;
        }

        .question-block:hover {
            border-color: var(--quiz-primary) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .question-badge {
            background: var(--quiz-primary);
            color: white;
            padding: 5px 15px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .answer-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }

        .btn-update {
            background: var(--quiz-gradient);
            color: white;
            padding: 12px 40px;
            border-radius: 12px;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.4);
            color: white;
        }

        .remove-question {
            border-radius: 8px;
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i> Edit Quiz</h2>
                <p class="mb-0 opacity-75">আপনি এখান থেকে কুইজের তথ্য এবং প্রশ্নগুলো পরিবর্তন করতে পারেন।</p>
            </div>
            <a href="{{ route('quiz.index') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Quizzes
            </a>
        </div>

        <form action="{{ route('quiz.update', $quiz->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card form-card mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Quiz Title</label>
                            <input type="text" name="title" value="{{ old('title', $quiz->title) }}"
                                class="form-control form-control-lg" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-12 mb-0">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="2" style="border-radius: 10px;">{{ old('description', $quiz->description) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label fw-bold">Start Date & Time</label>
                            <input type="datetime-local" name="start_exam_at"
                                value="{{ old('start_exam_at', $quiz->start_exam_at) }}"
                                class="form-control form-control-lg" placeholder="e.g. Basic Laravel Quiz" required
                                style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label fw-bold">End Date & Time</label>
                            <input type="datetime-local" name="end_exam_at"
                                value="{{ old('end_exam_at', $quiz->end_exam_at) }}" class="form-control form-control-lg"
                                placeholder="e.g. Basic Laravel Quiz" required style="border-radius: 10px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-4 text-dark"><i class="bi bi-list-check me-2 text-primary"></i> Questions & Answers
                </h4>
                <button type="submit" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">Update Quiz</button>
            </div>

            <div id="questions-wrapper">
                @foreach ($quiz->questions as $qIndex => $question)
                    <div class="question-block card mb-4 p-4 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="question-badge">Question #{{ $qIndex + 1 }}</span>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-question px-3">
                                <i class="bi bi-trash me-1"></i> Remove
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Question Text</label>
                            <input type="text" name="questions[{{ $qIndex }}][text]"
                                value="{{ $question->question }}" class="form-control" required style="border-radius: 8px;">
                        </div>

                        <div class="row">
                            @foreach ($question->answers as $aIndex => $answer)
                                <div class="col-md-6 mb-3">
                                    <div class="answer-group">
                                        <input type="text"
                                            name="questions[{{ $qIndex }}][answers][{{ $aIndex }}]"
                                            value="{{ $answer['answer'] }}" class="form-control mb-2" required
                                            style="border-radius: 8px;">
                                        <div class="form-check">
                                            <input type="radio" name="questions[{{ $qIndex }}][correct]"
                                                value="{{ $aIndex }}" class="form-check-input"
                                                {{ $answer['is_correct'] == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label text-success fw-bold small">Correct
                                                Answer</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
                <button type="button" class="btn btn-outline-primary fw-bold px-4 shadow-sm" id="add-question"
                    style="border-radius: 12px;">
                    <i class="bi bi-plus-lg me-2"></i> Add New Question
                </button>
                <button type="submit" class="btn btn-update shadow">
                    <i class="bi bi-cloud-arrow-up me-2"></i> Update Quiz
                </button>
            </div>
        </form>
    </div>

    <script>
        let questionIndex = {{ $quiz->questions->count() }};

        document.getElementById('add-question').addEventListener('click', () => {
            const wrapper = document.getElementById('questions-wrapper');
            const block = document.createElement('div');
            block.classList.add('question-block', 'card', 'mb-4', 'p-4', 'shadow-sm');

            let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="question-badge">Question #\${questionIndex + 1}</span>
                <button type="button" class="btn btn-outline-danger btn-sm remove-question px-3">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Question Text</label>
                <input type="text" name="questions[\${questionIndex}][text]" class="form-control" required style="border-radius: 8px;">
            </div>

            <div class="row">
        `;

            for (let i = 0; i < 4; i++) {
                html += `
                <div class="col-md-6 mb-3">
                    <div class="answer-group">
                        <input type="text" name="questions[\${questionIndex}][answers][\${i}]" class="form-control mb-2" required style="border-radius: 8px;">
                        <div class="form-check">
                            <input type="radio" name="questions[\${questionIndex}][correct]" value="\${i}" class="form-check-input" \${i === 0 ? 'checked' : ''}>
                            <label class="form-check-label text-success fw-bold small">Correct Answer</label>
                        </div>
                    </div>
                </div>
            `;
            }

            html += `</div>`;
            block.innerHTML = html;
            wrapper.appendChild(block);
            questionIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.remove-question')) {
                if (confirm('আপনি কি এই প্রশ্নটি মুছে ফেলতে চান?')) {
                    e.target.closest('.question-block').remove();
                }
            }
        });
    </script>
@endsection
