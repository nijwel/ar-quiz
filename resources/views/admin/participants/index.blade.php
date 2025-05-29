@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Participants</h2>
                    @session('success')
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endsession
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="250px">Name</th>
                            <th>Email</th>
                            <th>Total Marks</th>
                            <th>Total Exams</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $key => $participant)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $participant->name }}</td>
                                <td>{{ $participant->email }}</td>
                                <td>{{ totalUserMarks($participant->id) }}</td>
                                <td>{{ userQuizParticipants($participant->id) }}</td>
                                <td>
                                    <a href="{{ route('participant.view', $participant->id) }}"
                                        class="btn btn-sm btn-primary">Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $participants->links() }}
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total participants: {{ $participants->total() }}</span>
                    <span>Showing {{ $participants->count() }} of {{ $participants->total() }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
