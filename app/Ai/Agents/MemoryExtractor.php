<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class MemoryExtractor implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  list<string>  $known
     */
    public function __construct(public array $known = []) {}

    public function instructions(): Stringable|string
    {
        $known = $this->known === [] ? '(nothing yet)' : '- '.implode("\n- ", $this->known);

        return <<<PROMPT
            You extract long-term facts about the user from a conversation.

            Rules:
            - Only lasting facts and preferences, not one-off requests, moods or hypotheticals.
            - Each fact is one short sentence that makes sense on its own, starting with "The user".
            - Do not repeat facts that are already known.
            - Do not store health, financial or other sensitive details.
            - Return an empty list if there is nothing new.

            Already known:
            {$known}
            PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'facts' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
