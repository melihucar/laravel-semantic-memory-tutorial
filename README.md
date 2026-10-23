# Episode 1: memory tools

📖 **Article:** [Giving a Laravel AI Agent a Memory](https://melihucar.com/articles/giving-a-laravel-ai-agent-a-memory/)

> **Branches:** `main` always holds the latest published episode. Each episode also has its own branch: `episode-01`, `episode-02`, and so on, added as each episode is published.

Example code for a tutorial on giving a Laravel AI agent a memory, built on the official [Laravel AI SDK](https://laravel.com/docs/ai-sdk) (`laravel/ai`). The code is deliberately small: it shows the ideas, not a production-ready memory system.

An assistant that remembers facts about each user across conversations.

- **`save_memory` tool:** the agent saves a lasting fact when the user states one or asks it to remember something.
- **`recall_memory` tool:** the agent looks up what it knows before answering. Recall is a simple keyword search that falls back to the newest facts.
- **Background extraction:** after every reply, a queued job asks a cheaper model to pull out facts the agent missed.
- **Scoping:** both tools receive the signed-in user, so the model can never read or write another user's memories.

## Files

| File | What it does |
|---|---|
| `app/Ai/Agents/Assistant.php` | The chat agent, with conversation history and the two tools |
| `app/Ai/Tools/SaveMemory.php`, `RecallMemory.php` | The memory tools |
| `app/Ai/Agents/MemoryExtractor.php` | Structured-output agent that extracts facts |
| `app/Jobs/ExtractMemories.php` | Runs the extractor after each reply |
| `app/Http/Controllers/ChatController.php` | The `/chat` endpoint |
| `resources/views/chat.blade.php` | Demo chat page: conversations list, chat, and a live memories panel |
| `tests/Feature` | Pest tests, using the SDK's fakes |

## Run it

Requires PHP 8.3+ and Composer. It uses Anthropic by default; set `AI_PROVIDER` in `.env` to use any provider the Laravel AI SDK supports (OpenAI, Gemini, Mistral and others).

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

Add the API key for your provider (for example `ANTHROPIC_API_KEY`) to `.env`, then run these in two terminals:

```bash
php artisan serve
```

```bash
php artisan queue:work
```

Open http://127.0.0.1:8000 and log in with one of the seeded accounts. The password for each is `password`, and in the local environment the login page lists them:

| Account | Seeded memories |
|---|---|
| `alice@example.test` | prefers TypeScript, uses macOS |
| `bob@example.test` | writes Python, uses Windows |
| `carol@example.test` | none |

Open `/trail` to see each run, including every `save_memory` and `recall_memory` call.

## Tests

```bash
./vendor/bin/pest
```

## Known limits

These are what the next episode fixes:

- An extra model call after every reply.
- Keyword recall: "programming language" does not find "TypeScript".
- Facts that change ("uses Windows", then "uses macOS") are both kept.
