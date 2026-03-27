@props(['as' => 'ul'])

<{{ $as }} data-sortable {{ $attributes }}>
    {{ $slot }}
</{{ $as }}>
