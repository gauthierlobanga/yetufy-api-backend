<?php

namespace App;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class TenantPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $tenantId = tenancy()->initialized ? tenant('id') : 'central';

        return "{$tenantId}/{$media->model_type}/{$media->model_id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive/';
    }
}
