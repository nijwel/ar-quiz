{{-- Uploaded logo if there is one, otherwise the default bolt badge --}}
@props([
    'size' => 'size-10',
    'iconSize' => 'size-5',
    'tone' => 'bg-gradient-to-br from-brand-500 to-brand-800 shadow-lg shadow-brand-600/40',
])

@if ($brandLogoUrl)
    <img src="{{ $brandLogoUrl }}" alt="{{ config('app.name', 'QuizArena') }}"
        {{ $attributes->class([$size, 'shrink-0 rounded-xl object-contain']) }}>
@else
    <span {{ $attributes->class([$size, $tone, 'grid shrink-0 place-items-center rounded-xl text-snow']) }}>
        <span class="icon-[tabler--bolt-filled] {{ $iconSize }}"></span>
    </span>
@endif
