<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Expense extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'recorded_by',
        'category',
        'title',
        'amount',
        'incurred_on',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_on' => 'date',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk(config('filesystems.default'))
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ])
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->fit('contain', 300, 300)
                    ->queued();
            });
    }
}
