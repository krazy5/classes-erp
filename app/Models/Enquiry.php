<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'class_group_id',
        'subject_id',
        'source',
        'status',
        'assigned_to',
        'follow_up_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
