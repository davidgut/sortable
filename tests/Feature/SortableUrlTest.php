<?php

use DavidGut\Sortable\Tests\Fixtures\Post;
use Illuminate\Support\Facades\Blade;

it('generates sortable URL using config alias', function () {
    config(['sortable.models' => ['posts' => Post::class]]);

    $post = Post::create(['title' => 'Test Post']);

    expect($post->sortableUrl())->toBe(url('/sortable/posts/' . $post->id));
});

it('falls back to class basename when not in config', function () {
    config(['sortable.models' => []]);

    $post = Post::create(['title' => 'Test Post']);

    expect($post->sortableUrl())->toBe(url('/sortable/Post/' . $post->id));
});

it('compiles the blade directive', function () {
    $compiled = Blade::compileString('@sortableUrl($post)');

    expect($compiled)->toContain('data-sortable-update-url=');
    expect($compiled)->toContain('sortableUrl()');
});
