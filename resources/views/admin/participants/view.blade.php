@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>{{ $participant->name }} <small>({{ $participant->email }})</small> </h2>
                    @session('success')
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endsession
                </div>
            </div>
            <div class="card-body row">
                @foreach ($quizzes as $quiz)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{ $quiz->title }}</h5>
                                    <p class="text-muted float-right"> {{ quizResult($quiz->id, $participant->id) }}
                                        /{{ $quiz->questions->count() }}</p>
                                </div>
                            </div>
                            <div class="card-body">
                                @if (quizResult($quiz->id, $participant->id))
                                    <p><span class="text-secondary">Total Ques: {{ $quiz->questions->count() }} </span>
                                        <br>
                                        <span class="text-success">Total Answer:
                                            {{ totalAnswers($quiz->id, $participant->id) }} </span>
                                        <br>
                                        <span class="text-primary">Total Marks:
                                            {{ quizResult($quiz->id, $participant->id) }} </span>
                                        <br>
                                        <span class="text-success">Total Right:
                                            {{ rightAnswer($quiz->id, $participant->id) }}</span>
                                        <br>
                                        <span class="text-danger">Total Wrong:
                                            {{ wrongAnswer($quiz->id, $participant->id) }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
