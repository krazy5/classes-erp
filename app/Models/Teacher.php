<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Teacher extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['tenant_id', 'user_id', 'name', 'email', 'dob'];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships
    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
        $this->addMediaCollection('documents');
    }
}
