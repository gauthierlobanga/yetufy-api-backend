<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
// use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CreateTenantStorageSymlinks extends Command
{
    protected $signature = 'tenancy:create-symlinks';

    protected $description = 'Create storage symlinks for all existing tenants';

    public function handle(): int
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->createTenantStorageSymlink($tenant);
            $this->info("Symlink created for tenant: {$tenant->slug}");
        }

        $this->info('All tenant storage symlinks created successfully.');

        return self::SUCCESS;
    }

    private function createVendorStorageSymlink(Tenant $tenant): void
    {
        $tenantId = $tenant->id;
        $tenantSlug = $tenant->slug;
        $tenantStoragePath = storage_path('tenant'.$tenantId.'/app/public');
        $publicStoragePath = public_path('storage/tenant-'.$tenantSlug);

        // Créer le répertoire public/storage s'il n'existe pas
        if (! is_dir(public_path('storage'))) {
            mkdir(public_path('storage'), 0755, true);
        }

        // Supprimer le symlink s'il existe déjà
        if (is_link($publicStoragePath)) {
            unlink($publicStoragePath);
        } elseif (is_dir($publicStoragePath)) {
            // Si c'est un répertoire, le supprimer
            $this->deleteDirectory($publicStoragePath);
        }

        // Créer le symlink
        if (PHP_OS_FAMILY === 'Windows') {
            // Sur Windows, utiliser mklink /J pour créer une junction
            exec(sprintf('mklink /J "%s" "%s"', $publicStoragePath, $tenantStoragePath));
        } else {
            // Sur Linux/Mac, utiliser symlink
            symlink($tenantStoragePath, $publicStoragePath);
        }
    }

    private function createTenantStorageSymlink(Tenant $tenant): void
    {
        $tenantSlug = $tenant->slug;
        $tenantId = $tenant->id;

        // Dossier cible : storage/tenantXXXX/app/public
        $targetDir = storage_path('tenant'.$tenantId.'/app/public');

        // S'assurer que le dossier cible existe
        if (! is_dir($targetDir)) {
            if (! mkdir($targetDir, 0755, true)) {
                Log::error("Impossible de créer le dossier de stockage du tenant : {$targetDir}");

                return;
            }
        }

        // Chemin du lien public
        $publicLinkPath = public_path('storage/tenant-'.$tenantSlug);

        // S'assurer que le dossier public/storage existe
        $publicStorageDir = public_path('storage');
        if (! is_dir($publicStorageDir)) {
            mkdir($publicStorageDir, 0755, true);
        }

        // Supprimer l'ancien lien ou dossier s'il existe
        if (file_exists($publicLinkPath) || is_link($publicLinkPath)) {
            if (is_dir($publicLinkPath) && ! is_link($publicLinkPath)) {
                // C'est un vrai dossier (cas rare), on le supprime récursivement
                $this->deleteDirectory($publicLinkPath);
            } else {
                unlink($publicLinkPath);
            }
        }

        // Déterminer la cible relative selon l'OS
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows natif (hors Docker) : utiliser mklink /J avec chemin relatif
            $relativeTarget = str_replace('/', '\\', '../../storage/tenant'.$tenantId.'/app/public');
            $command = sprintf('mklink /J "%s" "%s"', $publicLinkPath, $relativeTarget);
            exec($command, $output, $returnCode);
            if ($returnCode !== 0) {
                Log::error("Échec de création du lien Windows pour le tenant {$tenantSlug}");
            }
        } else {
            // Linux / macOS / WSL / Docker : lien symbolique relatif
            $relativeTarget = '../../storage/tenant'.$tenantId.'/app/public';
            if (! symlink($relativeTarget, $publicLinkPath)) {
                Log::error("Échec de création du lien symbolique pour le tenant {$tenantSlug}");
            }
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
