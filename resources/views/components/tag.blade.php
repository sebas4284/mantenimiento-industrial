@props(['variant' => 'neutral'])

<span {{ $attributes->merge(['class' => "tag tag-$variant"]) }}>{{ $slot }}</span>
