<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function createConversation(User $user, string $title, array $messages): string
{
    $id = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $id, 'participant_type' => $user->getMorphClass(), 'participant_id' => $user->id,
        'title' => $title, 'created_at' => now(), 'updated_at' => now(),
    ]);

    foreach ($messages as [$role, $content]) {
        DB::table('agent_conversation_messages')->insert([
            'id' => (string) Str::uuid(), 'conversation_id' => $id,
            'participant_type' => $user->getMorphClass(), 'participant_id' => $user->id,
            'agent' => 'App\Ai\Agents\Assistant', 'role' => $role, 'content' => $content,
            'attachments' => '[]', 'steps' => '[]', 'usage' => '[]', 'meta' => '[]', 'status' => 'completed',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return $id;
}

it('lists only the user\'s own conversations', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    createConversation($alice, 'Git branch script', []);
    createConversation($bob, 'Python on a Mac', []);

    $this->actingAs($alice)->getJson('/conversations')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.title', 'Git branch script');
});

it('shows a conversation\'s messages to its owner only', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $id = createConversation($alice, 'Git branch script', [['user', 'Hi'], ['assistant', 'Hello!']]);

    $this->actingAs($alice)->getJson("/conversations/{$id}")
        ->assertOk()
        ->assertJsonPath('0.content', 'Hi')
        ->assertJsonPath('1.role', 'assistant');

    $this->actingAs($bob)->getJson("/conversations/{$id}")->assertNotFound();
});
