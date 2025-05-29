@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Edit Quiz</h2>
                <a href="{{ route('quiz.index') }}" class="btn btn-sm btn-secondary">Back to Quizzes</a>
            </div>
            <div class="card-body">
                <form action="{{ route('quiz.update', $quiz->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" value="{{ old('title', $quiz->title) }}" class="form-control"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $quiz->description) }}</textarea>
                    </div>

                    <div id="questions-wrapper" class="p-3 border mb-3">
                        <h4>Questions</h4>
                        @foreach ($quiz->questions as $qIndex => $question)
                            <div class="question-block border p-3 mb-3 bg-light">
                                <div class="mb-2">
                                    <label>Question Text</label>
                                    <input type="text" name="questions[{{ $qIndex }}][text]"
                                        value="{{ $question->question }}" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label>Answers</label>
                                    <div class="row mt-2">
                                        @foreach ($question->answers as $aIndex => $answer)
                                            <div class="col-md-6 mt-2">
                                                <input type="text"
                                                    name="questions[{{ $qIndex }}][answers][{{ $aIndex }}]"
                                                    value="{{ $answer['answer'] }}" class="form-control mb-2" required>
                                                <div class="form-check">
                                                    <input type="radio" name="questions[{{ $qIndex }}][correct]"
                                                        value="{{ $aIndex }}" class="form-check-input"
                                                        {{ $answer['is_correct'] == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label">Correct Answer</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm remove-question mt-3">Remove
                                    Question</button>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success" id="add-question">+ Add
                            Question</button>
                    </div>
                    <br><br>
                    <button type="submit" class="btn btn-primary">Update Quiz</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let questionIndex = {{ $quiz->questions->count() }};

        document.getElementById('add-question').addEventListener('click', () => {
            const wrapper = document.getElementById('questions-wrapper');
            const block = document.createElement('div');
            block.classList.add('question-block', 'border', 'p-3', 'mb-3', 'bg-light');

            let html = `
                <div class="mb-2">
                    <label>Question Text</label>
                    <input type="text" name="questions[\${questionIndex}][text]" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Answers</label>
                    <div class="row mt-2">
            `;

            for (let i = 0; i < 4; i++) {
                html += `
                    <div class="col-md-6 mt-2">
                        <input type="text" name="questions[\${questionIndex}][answers][\${i}]" class="form-control mb-2" required>
                        <div class="form-check">
                            <input type="radio" name="questions[\${questionIndex}][correct]" value="\${i}" class="form-check-input" \${i === 0 ? 'checked' : ''}>
                            <label class="form-check-label">Correct Answer</label>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-question mt-3">Remove Question</button>
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
