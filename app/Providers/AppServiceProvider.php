<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // The system administrator (R8) is allowed everything (Super Admin).
        Gate::before(function (User $user): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });

        // Log files carry stack traces, queries with their bindings, e-mail
        // addresses and IPs, so the viewer is restricted to R8. The package
        // only enforces its own gate in production, which is why the route
        // also carries the auth middleware in config/log-viewer.php.
        Gate::define('viewLogViewer', function (User $user): bool {
            return $user->isSuperAdmin();
        });
    }
}
