@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    @include('partials.profile', [
        'roleLabel' => 'Administrator',
        'profileRoute' => route('admin.profile.update'),
        'passwordRoute' => route('admin.password.update'),
        'emailEditable' => true,
    ])
@endsection
