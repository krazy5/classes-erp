<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Discount;
use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class FeeRecord extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'fee_structure_id',
        'total_amount',
        'is_paid',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    protected $appends = [
        'amount_paid',
        'outstanding_amount',
        'status',
        'discount_total',
        'fine_total',
        'net_amount',
        'net_outstanding',
    ];

    /**
     * A FeeRecord belongs to a single Student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * A FeeRecord can have many Installments (payments).
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function scopeForTenant($query, ?int $tenantId)
    {
        return $query->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId));
    }

    public function getAmountPaidAttribute(): float
    {
        $installments = $this->resolveInstallments();

        return round((float) $installments->sum('paid_amount'), 2);
    }

    public function getOutstandingAmountAttribute(): float
    {
        $due = (float) $this->total_amount;
        $paid = $this->amount_paid;

        return round(max(0, $due - $paid), 2);
    }

    public function getStatusAttribute(): string
    {
        $installments = $this->resolveInstallments();

        if ($this->total_amount <= 0) {
            return 'draft';
        }

        if ($this->outstanding_amount <= 0 && $installments->isNotEmpty()) {
            return 'paid';
        }

        if ($installments->contains(fn (Installment $installment) => $installment->is_overdue)) {
            return 'overdue';
        }

        if ($this->amount_paid > 0) {
            return 'partial';
        }

        return 'pending';
    }

    public function getDiscountTotalAttribute(): float
    {
        return round((float) $this->discounts->sum('amount'), 2);
    }

    public function getFineTotalAttribute(): float
    {
        $installments = $this->resolveInstallments()->loadMissing('fines');

        return round((float) $installments->sum(fn (Installment $installment) => $installment->fine_total), 2);
    }

    public function getNetAmountAttribute(): float
    {
        $base = (float) ($this->total_amount ?? 0);

        return round(max(0, $base + $this->fine_total - $this->discount_total), 2);
    }

    public function getNetOutstandingAttribute(): float
    {
        $baseOutstanding = $this->outstanding_amount;
        $installments = $this->resolveInstallments()->loadMissing('fines');
        $fineOutstanding = (float) $installments->sum(fn (Installment $installment) => $installment->fine_outstanding);

        return round(max(0, $baseOutstanding + $fineOutstanding - $this->discount_total), 2);
    }

    public function refreshPaymentStatus(): void
    {
        $installments = $this->resolveInstallments();

        $totalDue = $installments->sum('amount');
        $totalPaid = $installments->sum('paid_amount');
        $isSettled = $totalDue > 0 && $installments->isNotEmpty() && $totalPaid >= $totalDue
            && $installments->every->is_settled;

        $this->forceFill([
            'total_amount' => round((float) $totalDue, 2),
            'is_paid' => $isSettled,
        ])->saveQuietly();

        $this->resequenceInstallments();
    }

    public function resequenceInstallments(): void
    {
        $ordered = $this->installments()
            ->orderBy('sequence')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $sequence = 1;

        foreach ($ordered as $installment) {
            if ($installment->sequence === $sequence) {
                $sequence++;
                continue;
            }

            $installment->forceFill(['sequence' => $sequence++])->saveQuietly();
        }
    }

    protected function resolveInstallments(): Collection
    {
        if ($this->relationLoaded('installments')) {
            return $this->installments;
        }

        return $this->installments()->get();
    }
}
