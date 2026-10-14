<?php

namespace App\Providers;

use App\Models\User;
use App\Services\LocaleUrl;
use App\Services\MenuService;
use App\Services\SettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        // Role super_admin lolos seluruh Gate/Policies tanpa pengecekan permission.
        // Ability berbentuk "titik" (mis. user.view) dicek lewat RBAC permission.
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return $user->hasPermission($ability) ? true : null;
        });

        // Rate limiting login: 5 percobaan per menit per kombinasi email+IP.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        // Chrome publik: menu + pengaturan + switcher bahasa tersedia
        // di setiap view ber-layout publik.
        View::composer('layouts.public', function ($view) {
            $data = $view->getData();
            $locale = $data['locale'] ?? config('app.locale');
            $menus = app(MenuService::class);

            $view->with([
                'settings' => app(SettingsService::class),
                'locale' => $locale,
                'headerMenu' => $menus->tree('header', $locale),
                'footerMenu' => $menus->tree('footer', $locale),
                'localeSwitcher' => app(LocaleUrl::class),
            ]);
        });
    }
}
