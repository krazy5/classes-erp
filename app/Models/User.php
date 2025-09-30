<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'date_of_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The student profile linked to the user.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * The teacher profile linked to the user.
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Students linked to this user as a guardian.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'guardian_student', 'guardian_id', 'student_id')
                    ->withPivot('relationship_type')
                    ->withTimestamps();
    }

    /**
     * Guardians assigned to this user when the user is a student.
     */
    public function guardians()
    {
        return $this->belongsToMany(User::class, 'guardian_student', 'student_id', 'guardian_id')
                    ->withPivot('relationship_type')
                    ->withTimestamps();
    }


    public function wards()
{
    // guardian_id (this user) -> student_id (students.id)
    return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
        ->withPivot('relationship_type')
        ->withTimestamps();
}

/**
 * Guardians of this user when this user is a student.
 * If you actually need this on User, it must traverse via the Student profile.
 * Most projects keep this on Student (see Student::guardians()) and do:
 * $user->studentProfile?->guardians
 */
public function guardiansOfMe()
{
    // If the current user has a Student profile, return its guardians relation.
    // Useful as a convenience accessor; optional.
    return $this->studentProfile()
        ->one() // ensure single relation instance
        ->guardians();
}


}
