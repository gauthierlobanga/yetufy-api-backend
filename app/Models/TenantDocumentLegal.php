<?php

// app/Models/TenantDocumentLegal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantDocumentLegal extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'tenant_documents_legaux';

    protected $fillable = [
        'tenant_id',
        'type_document_id',
        'numero_document',
        'date_delivrance',
        'date_expiration',
        'lieu_delivrance',
        'autorite_delivrance',
        'metadata',
        'est_verifie',
        'verifie_le',
        'verifie_par',
        'vendor_request_id',
    ];

    protected $casts = [
        'date_delivrance' => 'date',
        'date_expiration' => 'date',
        'est_verifie' => 'boolean',
        'verifie_le' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocumentLegal::class, 'type_document_id');
    }

    public function verifiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifie_par');
    }

    public function vendorRequest(): BelongsTo
    {
        return $this->belongsTo(VendorRequest::class, 'vendor_request_id');
    }
}
