<?php

use DavidGut\Sortable\Tests\Fixtures\Post;
use Illuminate\Support\Facades\Blade;

it('renders the list component', function () {
    $html = Blade::render('<x-sortable::list>content</x-sortable::list>');

    expect($html)->toContain('<ul data-sortable');
    expect($html)->toContain('content');
    expect($html)->toContain('</ul>');
});

it('renders the list component with custom element', function () {
    $html = Blade::render('<x-sortable::list as="div">content</x-sortable::list>');

    expect($html)->toContain('<div data-sortable');
    expect($html)->toContain('</div>');
});

it('renders the item component', function () {
    config(['sortable.models' => ['posts' => Post::class]]);
    $post = Post::create(['title' => 'Test']);

    $html = Blade::render(
        '<x-sortable::item :model="$post">{{ $post->title }}</x-sortable::item>',
        ['post' => $post]
    );

    expect($html)->toContain('<li');
    expect($html)->toContain('data-sortable-update-url');
    expect($html)->toContain('Test');
    expect($html)->toContain('</li>');
});

it('renders the item component with custom element', function () {
    config(['sortable.models' => ['posts' => Post::class]]);
    $post = Post::create(['title' => 'Test']);

    $html = Blade::render(
        '<x-sortable::item as="div" :model="$post">content</x-sortable::item>',
        ['post' => $post]
    );

    expect($html)->toContain('<div');
    expect($html)->toContain('data-sortable-update-url');
    expect($html)->toContain('</div>');
});

it('renders the drag component with default icon', function () {
    $html = Blade::render('<x-sortable::drag />');

    expect($html)->toContain('<span');
    expect($html)->toContain('class="drag"');
    expect($html)->toContain('⠿');
});

it('renders the drag component with custom content', function () {
    $html = Blade::render('<x-sortable::drag>☰</x-sortable::drag>');

    expect($html)->toContain('☰');
    expect($html)->not->toContain('⠿');
});

it('renders the drag component with custom element', function () {
    $html = Blade::render('<x-sortable::drag as="button" />');

    expect($html)->toContain('<button');
    expect($html)->toContain('</button>');
});
