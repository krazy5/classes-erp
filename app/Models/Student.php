<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Student extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'dob',
        'gender',
        'address',
        'class_group_id'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_student')
                    ->withTimestamps();
    }

    public function feeRecords()
    {
        return $this->hasMany(FeeRecord::class);
    }

    public function testPerformances()
    {
        return $this->hasMany(TestPerformance::class);
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
        $this->addMediaCollection('documents');
    }

    public function guardians()
    {
        return $this->belongsToMany(User::class, 'guardian_student', 'student_id', 'guardian_id')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }
}
