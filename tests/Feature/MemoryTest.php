<?php

use App\Ai\Agents\MemoryExtractor;
use App\Jobs\ExtractMemories;
use App\Models\User;

it('stores extracted facts for the user', function () {
    MemoryExtractor::fake([
        ['facts' => ['The user prefers TypeScript over JavaScript.', 'The user uses macOS.']],
    ]);

    $user = User::factory()->create();

    ExtractMemories::dispatchSync($user, "Write it in TypeScript, I'm on a Mac now.", 'Sure, here it is.');

    expect($user->memories()->pluck('content')->all())->toBe([
        'The user prefers TypeScript over JavaScript.',
        'The user uses macOS.',
    ]);
});

it('tells the extractor what is already known', function () {
    $agent = new MemoryExtractor(known: ['The user uses macOS.']);

    expect((string) $agent->instructions())->toContain('- The user uses macOS.');
});

