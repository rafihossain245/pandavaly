<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Http\Middleware\EnsureBuyerAuthenticated;
use App\Http\Middleware\RedirectIfBuyerAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
         // Register Spatie permission middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'buyer.auth' => EnsureBuyerAuthenticated::class,
            'buyer.guest' => RedirectIfBuyerAuthenticated::class,
        ]);

        // Unauthenticated admin/staff users should be sent to the admin login page
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // PHP discards the whole request body once it exceeds post_max_size, so the form
        // arrives empty and the user gets a bare 413. Turn it into something actionable.
        $exceptions->render(function (PostTooLargeException $e, $request) {
            $message = 'The upload is too large. The server accepts up to '
                . ini_get('post_max_size') . ' per submission and '
                . ini_get('upload_max_filesize') . ' per file. Please upload smaller images.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 413);
            }

            return redirect()->back()->with('error', $message);
        });
    })->create();
