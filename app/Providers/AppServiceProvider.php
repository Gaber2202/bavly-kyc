<?php

namespace App\Providers;

use App\Models\KycRecord;
use App\Models\User;
use App\Policies\KycRecordPolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $locale = (string) config('app.locale');
        App::setLocale($locale);
        Carbon::setLocale($locale);

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(KycRecord::class, KycRecordPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        $this->registerKycRouteBinding();

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip().'|'.$request->string('username')->toString());
        });

        RateLimiter::for('admin-sensitive', function (Request $request): Limit {
            return Limit::perMinute(60)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('export', function (Request $request): Limit {
            return Limit::perMinute(10)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('reports', function (Request $request): Limit {
            return Limit::perMinute(90)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        Password::defaults(function () {
            return Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });
    }

    private function registerKycRouteBinding(): void
    {
        Route::bind('kyc', function (string $value) {
            $user = request()->user();
            abort_unless($user !== null, 401);

            /** @var KycRecord|null $record */
            $record = KycRecord::query()->find($value);
            abort_if($record === null, 404);

            abort_unless($user->can('view', $record), 404);

            return $record;
        });
    }
}
