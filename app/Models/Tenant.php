<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function getSettingsAttribute($value): array
    {
        $defaults = [
            'institute_name' => $this->attributes['name'] ?? null,
            'institute_logo_url' => null,
            'institute_logo_path' => null,
            'institute_logo_disk' => 'public',
            'institute_logo' => null,
            'academic_year' => null,
            'address' => null,
            'payment_gateway' => [
                'enabled' => false,
                'provider' => null,
                'meta' => [],
            ],
        ];

        $prepared = match (true) {
            is_array($value) => $value,
            is_string($value) && $value !== '' => json_decode($value, true) ?: [],
            default => [],
        };

        if (!empty($prepared['institute_logo']) && empty($prepared['institute_logo_url']) && empty($prepared['institute_logo_path'])) {
            $prepared['institute_logo_url'] = $prepared['institute_logo'];
        }

        $settings = array_replace_recursive($defaults, $prepared);

        if (empty($settings['institute_name'])) {
            $settings['institute_name'] = $this->attributes['name'] ?? null;
        }

        $settings['institute_logo'] = $this->resolveInstituteLogo($settings);

        return $settings;
    }

    public function updateSettings(array $attributes): void
    {
        $current = $this->settings;

        $this->forceFill([
            'settings' => array_replace_recursive($current, $attributes),
        ])->save();
    }

    protected function resolveInstituteLogo(array $settings): ?string
    {
        if (!empty($settings['institute_logo_url'])) {
            return $settings['institute_logo_url'];
        }

        if (!empty($settings['institute_logo_path'])) {
            $disk = $settings['institute_logo_disk'] ?? 'public';
            $path = $settings['institute_logo_path'];

            if ($path && Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        }

        return null;
    }
}
