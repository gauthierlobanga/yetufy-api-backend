<?php

namespace App\Http\Controllers\Api\Vendor\Settings;

use App\Events\VendorProfileUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParametresController extends Controller
{
    public function edit(Request $request): JsonResponse
    {
        return response()->json([
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $changes = [];

        if ($user->isDirty('name')) {
            $changes['name'] = true;
        }
        if ($user->isDirty('email')) {
            $changes['email'] = true;
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            if (Schema::hasColumn($user->getTable(), 'email_verifie')) {
                $user->email_verifie = false;
            }
        }

        $user->save();

        if (! empty($changes)) {
            event(new VendorProfileUpdated($user, $changes));
        }

        return to_route('tenant.profile.edit');
    }

    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('tenant.home');
    }

    private function syncCentralUserData($tenantUser, string $oldEmail, string $oldName): void
    {
        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        $userId = $tenantUser->id;

        $centralUser = DB::connection($centralConnection)
            ->table('users')
            ->where('id', $userId)
            ->first();

        if ($centralUser) {
            $updateData = [];
            if ($tenantUser->name !== $oldName) {
                $updateData['name'] = $tenantUser->name;
            }
            if ($tenantUser->email !== $oldEmail) {
                $updateData['email'] = $tenantUser->email;
                $updateData['email_verified_at'] = null;
                // Si vous avez une colonne email_verifie dans la base centrale
                if (Schema::hasColumn('users', 'email_verifie')) {
                    $updateData['email_verifie'] = false;
                }
            }
            if (! empty($updateData)) {
                DB::connection($centralConnection)
                    ->table('users')
                    ->where('id', $userId)
                    ->update($updateData);
            }
        }
    }
}
