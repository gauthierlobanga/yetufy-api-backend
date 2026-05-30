<?php

namespace App\Ai\Tools;

use App\Models\Client;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCustomerStats implements Tool
{
    public function description(): Stringable|string
    {
        return 'Renvoie le nombre total de clients et le nombre de nouveaux clients ce mois.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $total = Client::count();
        $newThisMonth = Client::whereMonth('created_at', now()->month)->count();

        return json_encode([
            'total_clients' => $total,
            'nouveaux_ce_mois' => $newThisMonth,
        ], JSON_UNESCAPED_UNICODE);
    }
}
