<?php

namespace App\Ai\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SaveMemory implements Tool
{
    public function __construct(private User $user) {}

    public function name(): string
    {
        return 'save_memory';
    }

    public function description(): Stringable|string
    {
        return 'Save a lasting fact about the user for future conversations. '
            .'Use it when the user states a lasting preference or fact, or asks you to remember something. '
            .'Do not save one-off requests, moods, hypotheticals or sensitive details.';
    }

    public function handle(Request $request): Stringable|string
    {
        $content = trim($request['content'] ?? '');

        if ($content === '') {
            return 'Nothing saved: the fact was empty.';
        }

        $exists = $this->user->memories()
            ->whereRaw('lower(content) = ?', [mb_strtolower($content)])
            ->exists();

        if ($exists) {
            return 'Already known.';
        }

        $this->user->memories()->create(['content' => $content, 'source' => 'tool:save_memory']);

        return 'Saved.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()
                ->description('One short sentence that makes sense on its own, starting with "The user".')
                ->required(),
        ];
    }
}
