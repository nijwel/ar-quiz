@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="justify-content-center">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>{{ $quiz->title }}</h2>
                        @session('success')
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endsession
                        <a href="{{ route('home') }}" class="btn btn-sm btn-success float-right">Home</a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.quiz.submit') }}" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}" hidden">
                            @foreach ($quiz->questions as $question)
                                <div class="col-lg-6">
                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <p>{{ $question->question }}</p>
                                            <input type="hidden" name="question_id[]" value="{{ $question->id }}" hidden">
                                        </div>
                                        <div class="card-body">
                                            @foreach ($question->answers as $answer)
                                                <label>
                                                    <input type="radio" name="answers[{{ $question->id }}]"
                                                        value="{{ $answer->id }}">
                                                    {{ $answer->answer }}
                                                </label><br>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-sm btn-primary mt-3" type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
