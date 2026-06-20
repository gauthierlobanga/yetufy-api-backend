<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Services\TenantThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorThemeController extends Controller
{
    public function update(Request $request)
    {
        $tenant = Auth::user()->tenants()->wherePivot('is_owner', true)->firstOrFail();

        $validated = $request->validate([
            'accent_color' => 'required|string',
            'neutral_color' => 'required|string',
        ]);

        // Générer la palette complète
        $themeService = app(TenantThemeService::class);
        $palette = $themeService->generatePalette($validated['accent_color'], $validated['neutral_color']);

        // Conserver les couleurs de base + la palette
        $theme = array_merge($palette, [
            'accent_color' => $validated['accent_color'],
            'neutral_color' => $validated['neutral_color'],
            'preset' => 'custom',
        ]);

        // Sauvegarder dans la configuration du tenant
        $tenant->updateTheme($theme);

        return response()->json(['success' => true, 'theme' => $tenant->theme]);
    }

    public function show()
    {
        // Dans un contexte tenant, utiliser le tenant actuel directement
        if (! function_exists('tenant') || ! tenant()) {
            return response()->json(['error' => 'Tenant non trouvé'], 404);
        }

        $tenant = tenant();

        Log::info('VendorThemeController@show - Tenant ID: '.$tenant->id);
        Log::info('VendorThemeController@show - Theme: '.json_encode($tenant->theme));

        return response()->json($tenant->theme);
    }
}
