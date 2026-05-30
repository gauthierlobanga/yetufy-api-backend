<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class ProductGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Tu es un rédacteur e‑commerce professionnel. Génère des fiches produits en français. Réponds uniquement avec le JSON structuré demandé.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'short_description' => $schema->string()->required(),
            'long_description' => $schema->string()->required(),
            'keywords' => $schema->array()->items($schema->string())->required(),
        ];
    }
}
