<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'student_id', 'date', 'present'];

    protected $casts = [
        'date' => 'date',
        'present' => 'boolean',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
