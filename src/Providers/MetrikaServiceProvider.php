<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Rovereto\Metrika\Http\Middleware\TrackStatistics;
use Rovereto\Metrika\Models\Agent;
use Rovereto\Metrika\Models\Device;
use Rovereto\Metrika\Models\Domain;
use Rovereto\Metrika\Models\Geoip;
use Rovereto\Metrika\Models\Hit;
use Rovereto\Metrika\Models\Path;
use Rovereto\Metrika\Models\Platform;
use Rovereto\Metrika\Models\Query;
use Rovereto\Metrika\Models\Referer;
use Rovereto\Metrika\Models\Route;
use Rovereto\Metrika\Models\Visit;
use Rovereto\Metrika\Models\Visitor;

class MetrikaServiceProvider extends ServiceProvider
{
    /**
     * Additional service providers to register for the environment.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../config/config.php'), 'metrika');

        // Bind eloquent models to IoC container
        $models = [
            'metrika.hit' => Hit::class,
            'metrika.path' => Path::class,
            'metrika.datum' => Datum::class,
            'metrika.geoip' => Geoip::class,
            'metrika.route' => Route::class,
            'metrika.agent' => Agent::class,
            'metrika.query' => Query::class,
            'metrika.visit' => Visit::class,
            'metrika.device' => Device::class,
            'metrika.domain' => Domain::class,
            'metrika.referer' => Referer::class,
            'metrika.visitor' => Visitor::class,
            'metrika.platform' => Platform::class,
        ];

        foreach ($models as $service => $class) {
            $this->app->singleton($service, $model = $this->app['config'][Str::replaceLast('.', '.models.', $service)]);
            $model === $class || $this->app->alias($service, $class);
        }

        $this->app->bind('Metrika', function () {
            return new \Rovereto\Metrika\Metrika;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router)
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('metrika.php'),
        ], 'config');


        $this->publishes([
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations')],
            'migrations'
        );

        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/metrika'),
        ]);

        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'metrika');

        // Push middleware to web group
        $router->pushMiddlewareToGroup('web', TrackStatistics::class);
    }
}
