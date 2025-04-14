<?php

use Router\Route;

class Loader
{
    public static function autoloader(): void
    {
        self::system();
        /*
         * Production mode
         * If the routes are cached, load the cached routes
         * sample: Route::loadCachedRoutes('storage/routes.cache');
         */
        if (file_exists('storage/routes.cache')) {
            Route::loadCachedRoutes('storage/routes.cache');
        } else {
            self::loadRoutes();
        }
        Route::run('/');
    }

    private static function system(): void
    {
        require_once 'vendor/autoload.php';
        require_once 'core/Router/RouteDefinition.php';
        require_once 'core/Router/Route.php';
    }

    private static function loadRoutes(): void
    {
        require_once 'routes/api.php';
        require_once 'routes/web.php';
        require_once 'storage/routes.cache.php';
    }
}