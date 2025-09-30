@extends('layouts.admin')

@section('title', 'My Profile | ' . config('app.name'))
@section('header', 'My Profile')

@section('content')
    @php
        $tenant = $user->tenant;
        $tenantSettings = $tenant?->settings ?? [];
    @endphp

    <div class="mx-auto flex max-w-3xl flex-col gap-6">
        <div class="rounded-lg border border-gray-200 bg-white/95 shadow-sm dark:border-gray-800 dark:bg-gray-900/90">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Profile information</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your personal details and contact information.</p>
            </div>
            <div class="px-6 py-5">
                @if(session('profileUpdated'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
                        Profile updated successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of birth</label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        @error('date_of_birth')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Save changes
                        </button>
                        <p class="text-xs text-gray-500 dark:text-gray-400">We will email notifications to this address.</p>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white/95 shadow-sm dark:border-gray-800 dark:bg-gray-900/90">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Password</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ensure your account uses a secure password.</p>
            </div>
            <div class="px-6 py-5">
                @if(session('passwordUpdated'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
                        Password updated successfully.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.password.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            Update password
                        </button>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tip: use a unique passphrase.</p>
                    </div>
                </form>
            </div>
        </div>

        @if($user->hasRole('admin') && $user->tenant)
            <div class="rounded-lg border border-gray-200 bg-white/95 shadow-sm dark:border-gray-800 dark:bg-gray-900/90">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Institute settings</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update tenant-wide information displayed across the portal.</p>
                </div>
                <div class="px-6 py-5">
                    @if(session('tenantSettingsUpdated'))
                        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-600 dark:bg-green-900/40 dark:text-green-300">
                            Tenant settings updated successfully.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.tenant.update') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Institute name</label>
                            <input type="text" name="institute_name" value="{{ old('institute_name', $tenantSettings['institute_name'] ?? $tenant?->name) }}" required class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            @error('institute_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Academic year</label>
                            <input type="text" name="academic_year" value="{{ old('academic_year', $tenantSettings['academic_year']) }}" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            @error('academic_year')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Institute logo URL</label>
                            <input type="url" name="institute_logo_url" value="{{ old('institute_logo_url', $tenantSettings['institute_logo_url']) }}" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Direct image link. Uploading a file overrides this URL.</p>
                            @error('institute_logo_url')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload institute logo</label>
                            <input type="file" name="institute_logo_file" accept="image/*" class="mt-1 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional — PNG or JPG up to 2MB.</p>
                            @error('institute_logo_file')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            @if(!empty($tenantSettings['institute_logo']))
                                <div class="mt-3 inline-flex items-center gap-3 rounded-md border border-gray-200 p-2 dark:border-gray-700">
                                    <img src="{{ $tenantSettings['institute_logo'] }}" alt="Institute logo preview" class="h-10 w-10 rounded object-cover">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Current logo</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Institute address</label>
                            <textarea name="address" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">{{ old('address', $tenantSettings['address']) }}</textarea>
                            @error('address')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <fieldset class="rounded-md border border-gray-200 p-4 dark:border-gray-800">
                            <legend class="px-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Payment gateway</legend>
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <input type="hidden" name="payment_gateway[enabled]" value="0">
                                    <input type="checkbox" name="payment_gateway[enabled]" value="1" @checked(old('payment_gateway.enabled', (bool)($tenantSettings['payment_gateway']['enabled'] ?? false))) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950">
                                    Enable online payments
                                </label>
                                <input type="text" name="payment_gateway[provider]" value="{{ old('payment_gateway.provider', $tenantSettings['payment_gateway']['provider'] ?? null) }}" placeholder="Provider name" class="flex-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                            </div>
                            @error('payment_gateway.provider')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                Save settings
                            </button>
                            <p class="text-xs text-gray-500 dark:text-gray-400">These values appear across the admin portal.</p>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
