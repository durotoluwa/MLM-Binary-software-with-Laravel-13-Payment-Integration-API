

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Spatie role/permission middleware
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,

            // Your custom middleware
            'ensure.payment' => \App\Http\Middleware\EnsurePaymentCompleted::class,
            'check.muted' => \App\Http\Middleware\CheckIfMuted::class,
            'check.pending.product.order' => \App\Http\Middleware\CheckPendingProductOrder::class,
            'ensure.superadmin' => \App\Http\Middleware\EnsureSuperadmin::class,

            // Impersonation middleware
          
            'impersonate.protect' => \Lab404\Impersonate\Middleware\ProtectFromImpersonation::class,

           
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();

