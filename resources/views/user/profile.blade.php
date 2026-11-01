@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    @include('partials.profile', [
        'roleLabel' => 'Student #' . auth()->user()->student_id,
        'profileRoute' => route('user.update.profile'),
        'passwordRoute' => route('user.update.password'),
        'emailEditable' => false,
    ])
@endsection
