@extends('layouts.app')

@section('title', 'Server Error')

@section('content')
    @include('errors._layout', [
        'code' => 500,
        'icon' => 'icon-[tabler--alert-triangle]',
        'title' => 'সার্ভারে সমস্যা হয়েছে!',
        'message' => 'আমাদের দিক থেকে কিছু একটা ভুল হয়েছে। দয়া করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
    ])
@endsection
