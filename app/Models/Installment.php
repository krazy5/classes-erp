<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Fine;

/** @mixin \Illuminate\Database\Eloquent\Builder */

class Installment extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'fee_record_id',
        'sequence',
        'amount',
        'paid_amount',
        'due_date',
        'paid_at',
        'payment_method',
        'reference',
        'receipt_number',
        'receipt_issued_at',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'receipt_issued_at' => 'datetime',
    ];

    protected $appends = [
        'is_paid',
        'is_settled',
        'is_overdue',
        'balance',
        'status',
        'fine_total',
        'fine_outstanding',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $installment) {
            if (!$installment->sequence) {
                $maxSequence = static::query()
                    ->where('fee_record_id', $installment->fee_record_id)
                    ->max('sequence');

                $installment->sequence = (int) $maxSequence + 1;
            }

            if ($installment->paid_at && !$installment->paid_amount) {
                $installment->paid_amount = $installment->amount;
            }

            if ($installment->paid_at && !$installment->receipt_issued_at) {
                $installment->receipt_issued_at = $installment->paid_at;
            }
        });

        static::updating(function (self $installment) {
            if ($installment->paid_at && !$installment->paid_amount) {
                $installment->paid_amount = $installment->amount;
            }

            if ($installment->paid_at && !$installment->receipt_issued_at) {
                $installment->receipt_issued_at = $installment->paid_at;
            }
        });

        static::saved(function (self $installment) {
            if ($installment->is_settled && !$installment->receipt_number) {
                $installment->forceFill([
                    'receipt_number' => $installment->generateReceiptNumber(),
                ])->saveQuietly();
            }

            $installment->feeRecord?->refreshPaymentStatus();
        });

        static::deleted(function (self $installment) {
            $installment->feeRecord?->refreshPaymentStatus();
        });
    }

    /**
     * An Installment belongs to a single FeeRecord.
     */
    public function feeRecord(): BelongsTo
    {
        return $this->belongsTo(FeeRecord::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function setPaidAmountAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['paid_amount'] = 0;

            return;
        }

        $rounded = round(max(0, (float) $value), 2);
        $dueAmount = (float) ($this->attributes['amount'] ?? $this->amount ?? 0);

        $this->attributes['paid_amount'] = $dueAmount > 0
            ? min($rounded, $dueAmount)
            : $rounded;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->is_settled;
    }

    public function getIsSettledAttribute(): bool
    {
        $paidAmount = (float) ($this->paid_amount ?? 0);
        $dueAmount = (float) ($this->amount ?? 0);

        return $this->paid_at !== null && $paidAmount >= $dueAmount && $dueAmount > 0;
    }

    public function getBalanceAttribute(): float
    {
        $dueAmount = (float) ($this->amount ?? 0);
        $paidAmount = (float) ($this->paid_amount ?? 0);

        return round(max(0, $dueAmount - $paidAmount), 2);
    }

    public function getFineTotalAttribute(): float
    {
        return round((float) $this->fines->sum('amount'), 2);
    }

    public function getFineOutstandingAttribute(): float
    {
        return round((float) $this->fines->sum(fn (Fine $fine) => $fine->outstanding_amount), 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_settled || !$this->due_date) {
            return false;
        }

        $dueDate = $this->due_date instanceof Carbon
            ? $this->due_date
            : Carbon::parse($this->due_date);

        return $dueDate->endOfDay()->isPast();
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_settled) {
            return 'settled';
        }

        if ($this->is_overdue) {
            return 'overdue';
        }

        if (($this->paid_amount ?? 0) > 0) {
            return 'partial';
        }

        return 'pending';
    }

    public function scopeOutstanding($query)
    {
        return $query->where(function ($builder) {
            $builder->whereNull('paid_at')
                ->orWhereColumn('paid_amount', '<', 'amount');
        });
    }

    public function applyPayment(array $attributes): void
    {
        $this->forceFill([
            'paid_at' => $attributes['paid_at'] ?? $this->paid_at,
            'paid_amount' => $attributes['paid_amount'] ?? $this->paid_amount,
            'payment_method' => $attributes['payment_method'] ?? $this->payment_method,
            'reference' => $attributes['reference'] ?? $this->reference,
            'remarks' => $attributes['remarks'] ?? $this->remarks,
            'receipt_number' => $attributes['receipt_number'] ?? $this->receipt_number,
            'receipt_issued_at' => $attributes['receipt_issued_at'] ?? $this->receipt_issued_at,
        ])->save();
    }

    public function generateReceiptNumber(): string
    {
        $timestamp = now()->format('ymd');
        $sequence = str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);

        return Str::upper("RCPT-{$timestamp}-{$sequence}");
    }
}
