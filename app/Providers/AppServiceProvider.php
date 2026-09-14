<?php

namespace App\Providers;

use App\Models\PlagiarismCheck;
use App\Policies\PlagiarismCheckPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        PlagiarismCheck::class => PlagiarismCheckPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(\App\Services\PlagiarismService::class);
        $this->app->singleton(\App\Services\JournalService::class);
        $this->app->singleton(\App\Services\ImprovementService::class);
        $this->app->singleton(\App\Services\HistoryService::class);
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Ensure admin user has UserSetting record with proper API keys for free check
        // This prevents NULL settings which causes missing elsevier and google scholar fallback
        $this->ensureAdminUserSetting();
    }

    /**
     * Ensure admin/system user has a UserSetting record with API keys
     * Required for FreeCheckController to work correctly (avoid NULL settings)
     */
    private function ensureAdminUserSetting(): void
    {
        try {
            $admin = \App\Models\User::where('role', 'admin')->first();
            
            if ($admin && !$admin->settings) {
                $admin->settings()->create([
                    'dark_mode' => false,
                    'email_notifications' => true,
                    'default_sources' => ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications'],
                    'serpapi_key' => config('services.serpapi.key'),
                    'elsevier_enabled' => (bool) config('services.elsevier.key'),
                    'elsevier_api_key' => config('services.elsevier.key'),
                ]);
            }
        } catch (\Exception $e) {
            // Silent fail - table might not exist yet during migrations
        }
    }
}
