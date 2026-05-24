<?php

namespace App\Providers;

use App\Repositories\VisitRepository;
use App\Repositories\VisitRepositoryInterface;
use App\Services\Clients\TwoIpClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VisitRepositoryInterface::class, VisitRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerTwoIpClient();
    }

    /**
     * Register TwoIpClient only if token is configured.
     */
    protected function registerTwoIpClient(): void
    {
        $token = config('services.2ip.token');

        if (!empty($token)) {
            $this->app->singleton(TwoIpClient::class, fn () => new TwoIpClient($token));
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
