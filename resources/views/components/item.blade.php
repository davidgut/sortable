@props(['as' => 'li', 'model'])

<{{ $as }} data-sortable-update-url="{{ $model->sortableUrl() }}" {{ $attributes }}>
    {{ $slot }}
</{{ $as }}>
