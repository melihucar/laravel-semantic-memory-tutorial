<?php

use App\Ai\Tools\RecallMemory;
use App\Ai\Tools\SaveMemory;
use App\Models\User;
use Laravel\Ai\Tools\Request;

it('saves a fact for the user', function () {
    $user = User::factory()->create();

    $result = (new SaveMemory($user))->handle(new Request(['content' => 'The user deploys with Laravel Forge.']));

    expect($result)->toBe('Saved.')
        ->and($user->memories()->pluck('content')->all())->toBe(['The user deploys with Laravel Forge.']);
});

it('does not save the same fact twice', function () {
    $user = User::factory()->create();
    $user->memories()->create(['content' => 'The user uses macOS.']);

    $result = (new SaveMemory($user))->handle(new Request(['content' => 'the user uses macOS.']));

    expect($result)->toBe('Already known.')
        ->and($user->memories()->count())->toBe(1);
});

it('recalls matching facts', function () {
    $user = User::factory()->create();
    $user->memories()->create(['content' => 'The user uses macOS.']);
    $user->memories()->create(['content' => 'The user prefers TypeScript over JavaScript.']);

    $result = (string) (new RecallMemory($user))->handle(new Request(['query' => 'typescript']));

    expect($result)->toContain('Matching facts')
        ->toContain('- The user prefers TypeScript over JavaScript.')
        ->not->toContain('macOS');
});

it('falls back to recent facts when nothing matches', function () {
    $user = User::factory()->create();
    $user->memories()->create(['content' => 'The user uses macOS.']);

    $result = (string) (new RecallMemory($user))->handle(new Request(['query' => 'favourite editor']));

    expect($result)->toContain('No direct match')->toContain('- The user uses macOS.');
});

it('never recalls another user\'s memories', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $alice->memories()->create(['content' => 'The user uses Windows.']);

    $result = (string) (new RecallMemory($bob))->handle(new Request(['query' => 'windows']));

    expect($result)->toBe('Nothing is known about this user yet.');
});
