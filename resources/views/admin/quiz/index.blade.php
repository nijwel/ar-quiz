@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Quizzes</h2>
                    @session('success')
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endsession
                    <a href="{{ route('quiz.create') }}" class="btn btn-sm btn-success float-right">Create New Quiz</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="250px">Title</th>
                            <th>Description</th>
                            <th width="150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizzes as $quiz)
                            <tr>
                                <td>{{ $quiz->id }}</td>
                                <td>{{ $quiz->title }}</td>
                                <td>{{ $quiz->description }}</td>
                                <td>
                                    <a href="{{ route('quiz.edit', $quiz->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $quizzes->links() }}
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total Quizzes: {{ $quizzes->total() }}</span>
                    <span>Showing {{ $quizzes->count() }} of {{ $quizzes->total() }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
