<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $user->update($validated);

        return back()->with('profileUpdated', true);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('passwordUpdated', true);
    }

    public function updateTenantSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->hasRole('admin'), 403);

        $data = $request->validate([
            'institute_name' => ['required', 'string', 'max:255'],
            'institute_logo_url' => ['nullable', 'url', 'max:255'],
            'institute_logo_file' => ['nullable', 'image', 'max:2048'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'payment_gateway' => ['nullable', 'array'],
            'payment_gateway.enabled' => ['nullable', 'boolean'],
            'payment_gateway.provider' => ['nullable', 'string', 'max:100'],
        ]);

        $tenant = $user->tenant;

        if ($tenant) {
            $settings = $this->normalizeSettingsPayload($data);

            if ($request->hasFile('institute_logo_file')) {
                $uploaded = $request->file('institute_logo_file');
                $path = $uploaded->store("tenant/{$tenant->id}/branding", 'public');

                if (!empty($tenant->settings['institute_logo_path'])) {
                    Storage::disk($tenant->settings['institute_logo_disk'] ?? 'public')
                        ->delete($tenant->settings['institute_logo_path']);
                }

                $settings['institute_logo_path'] = $path;
                $settings['institute_logo_disk'] = 'public';
                $settings['institute_logo_url'] = null;
            }

            unset($settings['institute_logo_file']);

            $tenant->updateSettings($settings);

            if (!empty($settings['institute_name']) && $settings['institute_name'] !== $tenant->name) {
                $tenant->forceFill(['name' => $settings['institute_name']])->save();
            }
        }

        return back()->with('tenantSettingsUpdated', true);
    }

    protected function normalizeSettingsPayload(array $data): array
    {
        $normalize = static function ($value) {
            if (is_string($value)) {
                $trimmed = trim($value);

                return $trimmed === '' ? null : $trimmed;
            }

            return $value;
        };

        $data['institute_name'] = $normalize($data['institute_name'] ?? null);
        $data['institute_logo_url'] = $normalize($data['institute_logo_url'] ?? null);
        $data['academic_year'] = $normalize($data['academic_year'] ?? null);
        $data['address'] = $normalize($data['address'] ?? null);

        if (isset($data['payment_gateway'])) {
            $gateway = $data['payment_gateway'];
            $gateway['enabled'] = !empty($gateway['enabled']);
            $gateway['provider'] = $normalize($gateway['provider'] ?? null);
            $gateway['meta'] = $gateway['meta'] ?? [];
            $data['payment_gateway'] = $gateway;
        }

        return $data;
    }
}
