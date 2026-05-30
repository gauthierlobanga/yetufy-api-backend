<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Ai\Agents\ProductGenerator;
use App\Ai\Agents\TenantAssistant;
use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TenantAiController extends Controller
{
    /**
     * Chat avec l'assistant IA.
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => ['required', 'string'],
            ]);

            $tenant = tenant();
            $user = Auth::user();

            $assistant = new TenantAssistant($tenant);

            if ($conversationId = $request->input('conversation_id')) {
                $assistant->continue($conversationId, as: $user);
            } else {
                $assistant->forUser($user);
            }

            $response = $assistant->prompt($validated['message']);

            return response()->json([
                'success' => true,
                'content' => (string) $response,
                'conversation_id' => $response->conversationId ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Chat Error', [
                'tenant_id' => tenant('id'),
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la communication avec l’assistant IA.',
            ], 500);
        }
    }

    /**
     * Récupère les recommandations récentes.
     */
    public function recommendations(): JsonResponse
    {
        $recommendations = Recommendation::query()
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Génère une fiche produit via IA.
     */
    public function generateProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:200'],
        ]);

        try {
            $agent = new ProductGenerator;
            $response = $agent->prompt($validated['prompt']);

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Product Generation Error', [
                'tenant_id' => tenant('id'),
                'user_id' => Auth::id(),
                'prompt' => $validated['prompt'],
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de générer le produit pour le moment.',
            ], 500);
        }
    }

    /**
     * Active ou désactive l'assistant IA du tenant.
     *
     * IMPORTANT :
     * - Si la requête provient d'Inertia, on retourne une redirection.
     * - Si la requête est une requête AJAX classique (fetch/axios), on retourne du JSON.
     */
    public function toggle(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $tenant = tenant();

        abort_unless($tenant, 404, 'Tenant introuvable.');

        $tenant->update([
            'ai_enabled' => $validated['enabled'],
        ]);

        $message = $validated['enabled']
            ? 'Assistant IA activé avec succès.'
            : 'Assistant IA désactivé avec succès.';

        // Si la requête vient d'Inertia, il faut retourner une redirection.
        if ($request->header('X-Inertia')) {
            return back()->with('success', $message);
        }

        // Pour les appels AJAX classiques.
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'ai_enabled' => (bool) $tenant->fresh()->ai_enabled,
            ],
        ]);
    }
}
