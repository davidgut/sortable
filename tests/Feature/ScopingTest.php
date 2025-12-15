<?php

use DavidGut\Sortable\Tests\Fixtures\GroupedPost;

it('scopes ordering by defined column', function () {
    // Category 1
    $post1 = GroupedPost::create(['title' => 'Post 1', 'category_id' => 1]); // 0
    $post2 = GroupedPost::create(['title' => 'Post 2', 'category_id' => 1]); // 1

    // Category 2
    $post3 = GroupedPost::create(['title' => 'Post 3', 'category_id' => 2]); // 0
    $post4 = GroupedPost::create(['title' => 'Post 4', 'category_id' => 2]); // 1

    expect($post1->position)->toBe(0);
    expect($post2->position)->toBe(1);
    expect($post3->position)->toBe(0); // Starts fresh for category 2
    expect($post4->position)->toBe(1);
});

it('moves ordering within scope', function () {
    // Category 1
    $post1 = GroupedPost::create(['title' => 'Post 1', 'category_id' => 1]); // 0
    $post2 = GroupedPost::create(['title' => 'Post 2', 'category_id' => 1]); // 1
    $post3 = GroupedPost::create(['title' => 'Post 3', 'category_id' => 1]); // 2

    // Category 2
    $post4 = GroupedPost::create(['title' => 'Post 4', 'category_id' => 2]); // 0

    // Build the query and inspect it to ensure scope is working
    $post1->setPosition(2);

    expect($post1->fresh()->position)->toBe(2);
    expect($post2->fresh()->position)->toBe(0);
    expect($post3->fresh()->position)->toBe(1);

    // Unaffected
    expect($post4->fresh()->position)->toBe(0);
});
