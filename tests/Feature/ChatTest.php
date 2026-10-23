<?php

use App\Ai\Agents\Assistant;
use App\Jobs\ExtractMemories;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Prompts\AgentPrompt;

it('gives the assistant memory tools scoped to the user and queues extraction', function () {
    Queue::fake();
    Assistant::fake(['Here is the script in TypeScript.']);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/chat', ['message' => 'Write me a script that renames files.'])
        ->assertOk()
        ->assertJsonPath('reply', 'Here is the script in TypeScript.');

    Assistant::assertPrompted(fn (AgentPrompt $prompt) => $prompt->agent->user->is($user)
        && $prompt->contains('Write me a script that renames files.'));

    Queue::assertPushed(ExtractMemories::class, fn (ExtractMemories $job) => $job->user->is($user));
});
