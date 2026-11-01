@extends('layouts.app')

@section('title', 'Create Quiz')

@section('content')
    <x-page-hero icon="icon-[tabler--square-rounded-plus]" title="Create New Quiz"
        subtitle="সঠিক তথ্য দিয়ে একটি নতুন কুইজ সেট তৈরি করুন।">
        <x-slot:actions>
            <a href="{{ route('quiz.index') }}" class="btn btn-glass">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back
            </a>
        </x-slot:actions>
    </x-page-hero>

    @include('admin.quiz._form', [
        'action' => route('quiz.store'),
        'submitLabel' => 'Save Quiz',
    ])
@endsection
