<?php

namespace App\Ai\Tools;

use App\Models\Promotion;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateDiscount implements Tool
{
    public function description(): Stringable|string
    {
        return 'Crée une nouvelle promotion pour la boutique. Retourne le code promo généré.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(['percentage', 'fixed'])->required(),
            'value' => $schema->number()->required(),
            'code' => $schema->string()->required()->description('Code promo unique'),
            'starts_at' => $schema->string()->required(),
            'ends_at' => $schema->string()->required(),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $promo = Promotion::create([
            'code' => $request['code'],
            'type' => $request['type'],
            'valeur' => $request['value'],
            'debut' => $request['starts_at'],
            'fin' => $request['ends_at'],
            'est_active' => true,
        ]);

        return "Promotion créée avec succès. Code : {$promo->code}";
    }
}
