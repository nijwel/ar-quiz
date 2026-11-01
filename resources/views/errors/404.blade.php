@extends('layouts.app')

@section('title', 'Not Found')

@section('content')
    @include('errors._layout', [
        'code' => 404,
        'icon' => 'icon-[tabler--map-question]',
        'title' => 'পেজটি খুঁজে পাওয়া যায়নি!',
        'message' => 'দুঃখিত, আপনি যে কুইজ বা পেজটি খুঁজছেন তা হয়তো মুছে ফেলা হয়েছে অথবা লিঙ্কটি ভুল।',
    ])
@endsection
