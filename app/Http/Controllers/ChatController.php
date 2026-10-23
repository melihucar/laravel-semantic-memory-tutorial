<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Assistant;
use App\Jobs\ExtractMemories;
use Illuminate\Http\Request;

class ChatController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $response = Assistant::make(user: $user)
            ->continueOrStart($data['conversation_id'] ?? null, as: $user)
            ->prompt($data['message']);

        ExtractMemories::dispatch($user, $data['message'], $response->text, $response->conversationId);

        return response()->json([
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
        ]);
    }
}
