@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="justify-content-center">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>Quizzes</h2>
                        <p>Total Marks: {{ $totalMarks }}</p>
                        @session('success')
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endsession
                        <a href="{{ route('home') }}" class="btn btn-sm btn-success float-right">Home</a>
                    </div>
                </div>
                <div class="card-body row">
                    @foreach ($quizzes as $quiz)
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>{{ $quiz->title }}</h5>
                                        <p class="text-muted float-right"> {{ quizResult($quiz->id, Auth::id()) }}
                                            /{{ $quiz->questions->count() }}</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p>{{ $quiz->description }}</p>

                                    @if (quizResult($quiz->id, Auth::id()))
                                        <p><span class="text-secondary">Total Ques: {{ $quiz->questions->count() }} </span>
                                            <br>
                                            <span class="text-success">Total Answer:
                                                {{ totalAnswers($quiz->id, Auth::id()) }} </span>
                                            <br>
                                            <span class="text-primary">Total Marks:
                                                {{ quizResult($quiz->id, Auth::id()) }} </span>
                                            <br>
                                            <span class="text-success">Total Right:
                                                {{ rightAnswer($quiz->id, Auth::id()) }}</span>
                                            <br>
                                            <span class="text-danger">Total Wrong:
                                                {{ wrongAnswer($quiz->id, Auth::id()) }}</span>
                                        </p>
                                    @endif

                                    @if (!quizResult($quiz->id, Auth::id()))
                                        <a href="{{ route('user.quiz.show', $quiz->slug) }}" class="btn btn-primary">Take
                                            Quiz</a>
                                    @else
                                        <a href="{{ route('user.quiz.show', $quiz->slug) }}"
                                            class="btn btn-primary disabled">Take
                                            Quiz</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
