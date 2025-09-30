<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'fee_record_id',
        'granted_by',
        'type',
        'amount',
        'reason',
        'granted_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'granted_at' => 'date',
    ];

    public function feeRecord(): BelongsTo
    {
        return $this->belongsTo(FeeRecord::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
