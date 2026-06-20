<?php

use App\Http\Controllers\Api\Auth\TenantSsoLoginController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/tenant-sso-login', TenantSsoLoginController::class)
    ->name('tenant.sso');

Route::get('/storage/tenant-{slug}/{path}', function ($slug, $path) {
    $tenant = Tenant::where('slug', $slug)->first();

    if (! $tenant) {
        abort(404);
    }

    tenancy()->initialize($tenant);

    $disk = Storage::disk('public');

    if (! $disk->exists($path)) {
        abort(404);
    }

    $file = $disk->path($path);
    $mimeType = mime_content_type($file);

    return new BinaryFileResponse($file, 200, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="'.basename($file).'"',
    ]);
})->where('path', '.*')->name('tenant.storage');
