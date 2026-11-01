@extends('layouts.app')

@section('title', 'Edit Quiz')

@section('content')
    <x-page-hero icon="icon-[tabler--pencil]" eyebrow="Editing" title="{{ $quiz->title }}"
        subtitle="এখান থেকে কুইজের তথ্য এবং প্রশ্নগুলো পরিবর্তন করতে পারেন।">
        <x-slot:actions>
            <a href="{{ route('quiz.index') }}" class="btn btn-glass">
                <span class="icon-[tabler--arrow-left] size-4"></span> Back to Quizzes
            </a>
        </x-slot:actions>
    </x-page-hero>

    @include('admin.quiz._form', [
        'action' => route('quiz.update', $quiz->id),
        'submitLabel' => 'Update Quiz',
    ])
@endsection
