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
            position: relative;
        }

        .question-block:hover {
            border-color: var(--quiz-primary) !important;
        }

        .question-number {
            background: var(--quiz-primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .answer-input-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .btn-submit {
            background: var(--quiz-gradient);
            color: white;
            padding: 12px 40px;
            border-radius: 12px;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.4);
            color: white;
        }

        .remove-question {
            border-radius: 8px;
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-plus-circle me-2"></i> Create New Quiz</h2>
                <p class="mb-0 opacity-75">সঠিক তথ্য দিয়ে একটি নতুন কুইজ সেট তৈরি করুন।</p>
            </div>
            <a href="{{ route('quiz.index') }}" class="btn btn-light fw-bold rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('quiz.store') }}" method="POST">
            @csrf

            <div class="card form-card mb-4">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Quiz Title</label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="form-control form-control-lg" placeholder="e.g. Basic Laravel Quiz" required
                                style="border-radius: 10px;">
                        </div>
                        <div class="col-md-12 mb-0">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Briefly describe what this quiz is about..." style="border-radius: 10px;">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div id="questions-wrapper">
                <div class="question-block card mb-4 p-4 shadow-sm bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-primary">
                            <span class="question-number text-white">1</span> Question Details
                        </h5>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Question Text</label>
                        <input type="text" name="questions[0][text]" value="{{ old('questions.0.text') }}"
                            class="form-control" placeholder="What is the capital of...?" required
                            style="border-radius: 8px;">
                    </div>

                    <div class="row">
                        <label class="form-label fw-semibold mb-3">Answer Options (Select the correct one)</label>
                        @for ($i = 0; $i < 4; $i++)
                            <div class="col-md-6 mb-3">
                                <div class="answer-input-group border">
                                    <input type="text" name="questions[0][answers][{{ $i }}]"
                                        class="form-control mb-2" placeholder="Option {{ $i + 1 }}" required
                                        style="border-radius: 8px;">
                                    <div class="form-check">
                                        <input type="radio" name="questions[0][correct]" value="{{ $i }}"
                                            class="form-check-input" {{ $i == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label text-success fw-bold small">Correct Answer</label>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-5">
                <button type="button" class="btn btn-outline-primary fw-bold px-4" id="add-question"
                    style="border-radius: 12px;">
                    <i class="bi bi-plus-lg me-2"></i> Add Another Question
                </button>
                <button type="submit" class="btn btn-submit shadow">
                    <i class="bi bi-check2-circle me-2"></i> Save Quiz Now
                </button>
            </div>
        </form>
    </div>

    <script>
        let questionIndex = 1;

        document.getElementById('add-question').addEventListener('click', () => {
            const wrapper = document.getElementById('questions-wrapper');
            const block = document.createElement('div');
            block.classList.add('question-block', 'card', 'mb-4', 'p-4', 'shadow-sm', 'bg-white');

            let html = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-primary">
                    <span class="question-number text-white">${questionIndex + 1}</span> Question Details
                </h5>
                <button type="button" class="btn btn-outline-danger btn-sm remove-question px-3">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Question Text</label>
                <input type="text" name="questions[${questionIndex}][text]" class="form-control" placeholder="Enter question..." required style="border-radius: 8px;">
            </div>

            <div class="row">
                <label class="form-label fw-semibold mb-3">Answer Options</label>
        `;

            for (let i = 0; i < 4; i++) {
                html += `
                <div class="col-md-6 mb-3">
                    <div class="answer-input-group border">
                        <input type="text" name="questions[${questionIndex}][answers][${i}]" class="form-control mb-2" placeholder="Option ${i+1}" required style="border-radius: 8px;">
                        <div class="form-check">
                            <input type="radio" name="questions[${questionIndex}][correct]" value="${i}" class="form-check-input" ${i == 0 ? 'checked' : ''}>
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
                e.target.closest('.question-block').remove();
                // Optional: Re-number the remaining questions
            }
        });
    </script>
@endsection
