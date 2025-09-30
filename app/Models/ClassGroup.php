<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassGroup extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'name'];

    // Relationships
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_class_group')
                    ->withTimestamps();
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}
