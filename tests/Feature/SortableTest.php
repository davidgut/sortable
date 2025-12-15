<?php

use DavidGut\Sortable\Tests\Fixtures\Post;

it('sets order automatically on creation', function () {
    $post1 = Post::create(['title' => 'Post 1']);
    $post2 = Post::create(['title' => 'Post 2']);
    $post3 = Post::create(['title' => 'Post 3']);

    expect($post1->position)->toBe(0);
    expect($post2->position)->toBe(1);
    expect($post3->position)->toBe(2);
});

it('can move a model down', function () {
    $post1 = Post::create(['title' => 'Post 1']); // 0
    $post2 = Post::create(['title' => 'Post 2']); // 1
    $post3 = Post::create(['title' => 'Post 3']); // 2

    $post1->setPosition(2);

    expect($post1->fresh()->position)->toBe(2);
    expect($post2->fresh()->position)->toBe(0);
    expect($post3->fresh()->position)->toBe(1);
});

it('can move a model up', function () {
    $post1 = Post::create(['title' => 'Post 1']); // 0
    $post2 = Post::create(['title' => 'Post 2']); // 1
    $post3 = Post::create(['title' => 'Post 3']); // 2

    $post3->setPosition(0);

    expect($post1->fresh()->position)->toBe(1);
    expect($post2->fresh()->position)->toBe(2);
    expect($post3->fresh()->position)->toBe(0);
});

it('can sort query', function () {
    Post::create(['title' => 'Post 1']);
    Post::create(['title' => 'Post 2']);
    Post::create(['title' => 'Post 3']);

    $titles = Post::sorted()->pluck('title')->toArray();

    expect($titles)->toBe(['Post 1', 'Post 2', 'Post 3']);

    $titlesDesc = Post::sorted('desc')->pluck('title')->toArray();
    expect($titlesDesc)->toBe(['Post 3', 'Post 2', 'Post 1']);
});
