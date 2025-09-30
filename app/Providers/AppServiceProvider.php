<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\TestPerformance;
use App\Observers\ExamObserver;
use App\Observers\MarkObserver;
use App\Policies\TestPerformancePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $defaultAppName = config('app.name');

        View::composer('*', function ($view) use ($defaultAppName) {
            $user = auth()->user();
            $tenant = $user?->tenant;
            $settings = $tenant?->settings ?? [];

            if ($tenant && empty($settings['institute_name'])) {
                $settings['institute_name'] = $tenant->name;
            }

            $instituteName = $settings['institute_name'] ?? $tenant?->name ?? $defaultAppName;

            config()->set('app.name', $instituteName);

            $view->with('tenantSettings', $settings);
            $view->with('institutionName', $instituteName);
        });

        Gate::policy(TestPerformance::class, TestPerformancePolicy::class);

        Mark::observe(MarkObserver::class);
        Exam::observe(ExamObserver::class);
    }
}
