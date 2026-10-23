<?php

namespace App\Jobs;

use App\Ai\Agents\MemoryExtractor;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExtractMemories implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $message,
        public string $reply,
        public ?string $conversationId = null,
    ) {}

    public function handle(): void
    {
        $known = $this->user->memories()->pluck('content')->all();

        $response = MemoryExtractor::make(known: $known)->prompt(
            "User: {$this->message}\n\nAssistant: {$this->reply}"
        );

        foreach ($response['facts'] ?? [] as $fact) {
            if (is_string($fact) && trim($fact) !== '') {
                $this->user->memories()->create([
                    'content' => trim($fact),
                    'source' => $this->conversationId ? "conversation:{$this->conversationId}" : null,
                ]);
            }
        }
    }
}
