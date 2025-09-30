<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'class_group_id',
        'subject_id',
        'amount',
        'frequency',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function feeRecords()
    {
        return $this->hasMany(FeeRecord::class);
    }
}
