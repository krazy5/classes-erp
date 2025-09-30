<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'title', 'body', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Relationships
    public function students()
    {
        return $this->belongsToMany(Student::class, 'announcement_student')->withTimestamps();
    }

    public function classGroups()
    {
        return $this->belongsToMany(ClassGroup::class, 'announcement_class_group')->withTimestamps();
    }
}
