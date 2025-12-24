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
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background: #fff;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: var(--quiz-primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 20px;
            border: none;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-upload {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: white;
            backdrop-filter: blur(5px);
        }

        .btn-upload:hover {
            background: white;
            color: var(--quiz-primary);
        }

        .modal-content {
            border: none;
            border-radius: 20px;
        }

        .modal-header {
            background: var(--quiz-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>

    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-journal-text me-2"></i> Quiz Management</h2>
                <p class="mb-0 opacity-75">নতুন কুইজ তৈরি করুন অথবা ফাইল আপলোড করে কুইজ যোগ করুন।</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-upload fw-bold px-4 rounded-pill" data-bs-toggle="modal"
                    data-bs-target="#staticBackdrop">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload Quiz
                </button>
                <a href="{{ route('quiz.create') }}" class="btn btn-light fw-bold px-4 rounded-pill text-primary shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i> Create New
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 15px;">
                <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card quiz-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Quiz Details</th>
                                <th>Description</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($quizzes as $quiz)
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#{{ $quiz->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6">{{ $quiz->title }}</div>
                                        <small class="text-muted"><i class="bi bi-question-circle me-1"></i>
                                            {{ $quiz->questions_count }} Questions</small>
                                    </td>
                                    <td class="text-muted small" style="max-width: 300px;">
                                        {{ Str::limit($quiz->description, 80) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('quiz.edit', $quiz->id) }}"
                                                class="btn btn-action btn-outline-primary" title="Edit Quiz">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST"
                                                onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই কুইজটি ডিলিট করতে চান?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action btn-outline-danger"
                                                    title="Delete Quiz">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted small">
                        Showing <b>{{ $quizzes->count() }}</b> of <b>{{ $quizzes->total() }}</b> quizzes
                    </div>
                    <div>{{ $quizzes->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up me-2"></i> Upload Quiz File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('quiz.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-file-earmark-code text-primary" style="font-size: 3rem;"></i>
                                <p class="text-muted small mt-2">দয়া করে শুধু JSON ফরম্যাট ফাইল আপলোড করুন।</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select JSON File</label>
                                <input class="form-control" type="file" name="quiz_file" accept=".json" required
                                    style="border-radius: 10px;">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal"
                                style="border-radius: 10px;">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4"
                                style="border-radius: 10px; background: var(--quiz-primary);">Upload Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
