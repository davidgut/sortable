@props(['as' => 'span'])

<{{ $as }} class="drag" {{ $attributes }}>
    {{ $slot->isEmpty() ? '⠿' : $slot }}
</{{ $as }}>
