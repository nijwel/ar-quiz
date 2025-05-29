@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="card">
            @foreach ($errors->all() as $error)
                <div class="text-center p-3">
                    <div class="text-danger" role="alert">
                        *{{ $error }}
                    </div>
                </div>
            @endforeach

            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Create New Quiz</h2>
                    @session('success')
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endsession
                    <a href="{{ route('quiz.index') }}" class="btn btn-sm btn-secondary">Back to Quizzes</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('quiz.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div id="questions-wrapper" class="p-3 mb-3">
                        <h4>Questions</h4>
                        <div class="question-block border p-3 mb-3">
                            <div class="question-block border p-3 mb-3 bg-light">
                                <div class="mb-2">
                                    <label>Question Text</label>
                                    <input type="text" name="questions[0][text]" value="{{ old('questions.0.text') }}"
                                        class="form-control" required>
                                </div>
                                <br>
                                <div class="mb-3">
                                    <label>Answers</label>
                                    <div class="row mt-2">
                                        @for ($i = 0; $i < 4; $i++)
                                            <div class="col-md-6">
                                                <input type="text" name="questions[0][answers][{{ $i }}]"
                                                    class="form-control mb-2" required>
                                                <div class="form-check">
                                                    <input type="radio" name="questions[0][correct]"
                                                        value="{{ $i }}" class="form-check-input"
                                                        {{ $i == 0 || old('questions.0.correct') == $i ? 'checked' : '' }}>
                                                    <label class="form-check-label">Correct Answer</label>
                                                    <br>
                                                    <br>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success" id="add-question">+ Add
                            Question</button>
                    </div>
                    <br><br>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

            </div>
        </div>

    </div>

    <script>
        let questionIndex = 1;

        document.getElementById('add-question').addEventListener('click', () => {
            const wrapper = document.getElementById('questions-wrapper');
            const block = document.createElement('div');
            block.classList.add('question-block', 'border', 'p-3', 'mb-3');

            let html = `
            <div class="question-block border p-3 mb-3 bg-light">
                <div class="mb-2">
                    <label>Question Text</label>
                    <input type="text" name="questions[${questionIndex}][text]" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Answers</label>
                    <div class="row mt-2">
            `;

            for (let i = 0; i < 4; i++) {
                html += `
                    <div class="col-md-6 mt-2">
                        <input type="text" name="questions[${questionIndex}][answers][${i}]" class="form-control mb-2" required>
                        <div class="form-check">
                            <input type="radio" name="questions[${questionIndex}][correct]" value="${i}" class="form-check-input" ${i == 0 ? 'checked' : ''}>
                            <label class="form-check-label">Correct Answer</label>
                            <br>
                            <br>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>

                <button type="button" class="btn btn-danger btn-sm remove-question mt-3">Remove Question</button>
            </div>
            `;

            block.innerHTML = html;
            wrapper.appendChild(block);

            questionIndex++;
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-question')) {
                e.target.closest('.question-block').remove();
            }
        });
    </script>
@endsection
