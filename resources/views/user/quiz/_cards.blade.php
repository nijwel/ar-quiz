{{-- One page of exam cards. Also returned on its own for "load more". --}}
@foreach ($quizzes as $quiz)
    <x-quiz-card :quiz="$quiz" :result="$quizResults[$quiz->id]" :delay="min($loop->index, 8) * 60" />
@endforeach
