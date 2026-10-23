<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Ai\Models\Conversation;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/', 'chat');
    Route::post('/chat', ChatController::class);

    Route::get('/conversations', fn (Request $request) => Conversation::query()
        ->where('participant_type', $request->user()->getMorphClass())
        ->where('participant_id', $request->user()->getKey())
        ->latest('updated_at')
        ->get(['id', 'title', 'updated_at']));

    Route::get('/conversations/{conversation}', function (Request $request, Conversation $conversation) {
        abort_unless($conversation->participant_type === $request->user()->getMorphClass()
            && (int) $conversation->participant_id === $request->user()->getKey(), 404);

        return $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get(['role', 'content', 'created_at']);
    });

    Route::get('/memories', fn (Request $request) => $request->user()->memories()
        ->latest('updated_at')
        ->get(['id', 'content', 'source', 'updated_at']));

    Route::delete('/memories', function (Request $request) {
        $request->user()->memories()->delete();

        return response()->noContent();
    });
});
