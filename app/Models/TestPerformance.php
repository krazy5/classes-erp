<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TestPerformance extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_group_id',
        'subject_id',
        'recorded_by',
        'title',
        'assessment_type',
        'term',
        'test_date',
        'max_score',
        'score',
        'percentage',
        'grade',
        'metadata',
        'remarks',
    ];

    protected $casts = [
        'metadata' => 'array',
        'test_date' => 'date',
        'max_score' => 'decimal:2',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $performance): void {
            if (!$performance->class_group_id && $performance->student) {
                $performance->class_group_id = $performance->student->class_group_id;
            }

            if ($performance->max_score !== null && $performance->score !== null && $performance->max_score > 0) {
                $performance->percentage = round(($performance->score / $performance->max_score) * 100, 2);
            }

            if (!$performance->grade && $performance->percentage !== null) {
                $performance->grade = static::gradeForPercentage((float) $performance->percentage);
            }

            if ($performance->metadata) {
                $performance->metadata = Arr::where($performance->metadata, fn ($value) => $value !== null && $value !== '');
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk(config('filesystems.default'))
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->fit('contain', 320, 320)
                    ->queued();
            });
    }

    protected static function gradeForPercentage(float $percentage): ?string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            $percentage > 0 => 'E',
            default => null,
        };
    }
}
