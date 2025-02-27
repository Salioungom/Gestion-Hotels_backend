<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    // protected $middleware = [
    //     // Si tu veux ajouter un middleware global (sur toutes les requêtes)
    //     // \App\Http\Middleware\CorsMiddleware::class,  // Tu peux aussi le mettre ici pour toutes les requêtes
    // ];

    protected $middleware = [
        \App\Http\Middleware\CorsMiddleware::class,
    ];
    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // Web middleware group
        ],

        'api' => [
            \App\Http\Middleware\CorsMiddleware::class,  // Ajout de ton middleware CORS personnalisé
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // Liste des middlewares individuels, si tu en as besoin
    ];
}
