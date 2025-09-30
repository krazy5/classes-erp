<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'installment_id',
        'amount',
        'paid_amount',
        'assessed_at',
        'paid_at',
        'reason',
        'notes',
        'waived_at',
        'waived_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'assessed_at' => 'date',
        'paid_at' => 'datetime',
        'waived_at' => 'datetime',
    ];

    protected $appends = [
        'outstanding_amount',
        'is_paid',
        'is_waived',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function getOutstandingAmountAttribute(): float
    {
        $due = (float) ($this->amount ?? 0);
        $paid = (float) ($this->paid_amount ?? 0);

        return round(max(0, $due - $paid), 2);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->outstanding_amount <= 0 && $this->amount > 0 && !$this->is_waived;
    }

    public function getIsWaivedAttribute(): bool
    {
        return $this->waived_at !== null;
    }

    public function markPaid(float $amount): void
    {
        $paid = round(max(0, min($amount, (float) $this->amount)), 2);

        $this->forceFill([
            'paid_amount' => $paid,
            'paid_at' => now(),
        ])->save();
    }
}
