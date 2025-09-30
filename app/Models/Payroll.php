<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'payable_type',
        'payable_id',
        'amount',
        'period_start',
        'period_end',
        'due_on',
        'paid_at',
        'payment_method',
        'reference',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_on' => 'date',
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markPaid(?\DateTimeInterface $paidAt = null): void
    {
        $this->forceFill([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
        ])->save();
    }
}
