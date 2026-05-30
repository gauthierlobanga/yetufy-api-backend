<?php

// app/Models/VendorRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'plan_id',
        'tenant_id',
        'shop_name',
        'shop_slug',
        'shop_description',
        'contact_email',
        'contact_phone',
        'status',
        'rejection_reason',
        'payment_session_id',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Constantes de statut
    public const string STATUS_PENDING = 'pending';

    public const string STATUS_PAYMENT_PENDING = 'payment_pending';

    public const string STATUS_APPROVED = 'approved';

    public const string STATUS_REJECTED = 'rejected';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PAYMENT_PENDING => 'Paiement en attente',
            self::STATUS_APPROVED => 'Approuvée',
            self::STATUS_REJECTED => 'Rejetée',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // App\Models\VendorRequest

    public function documents(): HasMany
    {
        return $this->hasMany(TenantDocumentLegal::class, 'vendor_request_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Accesseurs
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getDomainAttribute(): string
    {
        return $this->shop_slug.'.'.config('app.domain');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaymentPending($query)
    {
        return $query->where('status', self::STATUS_PAYMENT_PENDING);
    }
}
