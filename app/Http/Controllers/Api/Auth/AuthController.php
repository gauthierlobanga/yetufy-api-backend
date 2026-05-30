<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantPropsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    private TenantPropsService $tenantPropsService;
    private Tenant $tennt;
    public function __construct(TenantPropsService $tenantPropsService, Tenant $tennt)
    {
        $this->tenantPropsService = $tenantPropsService;
        $this->tennt = $tennt;
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => $user->load('tenants'),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $user = app(CreateNewUser::class)->create($request->all());
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = tenant(); // Tenant $tenant;

        return response()->json([
            'user' => $user?->loadMissing('tenants'),
            'tenant' => function_exists('tenant') && tenant() ? $this->tenantPropsService->getTenantProps($tenant) : null,
        ]);
    }
}
