<?php

namespace App\Ai\Tools;

use App\Models\Commande;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetRevenueStats implements Tool
{
    public function description(): Stringable|string
    {
        return "Fournit le chiffre d'affaires du jour, du mois et le total depuis le début.";
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $today = Commande::whereDate('created_at', today())->sum('total');
        $month = Commande::whereMonth('created_at', now()->month)->sum('total');
        $all = Commande::sum('total');

        return json_encode([
            'aujourd_hui' => round($today, 2),
            'ce_mois' => round($month, 2),
            'total' => round($all, 2),
        ], JSON_UNESCAPED_UNICODE);
    }
}
