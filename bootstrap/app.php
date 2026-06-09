<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'tenant' => \App\Http\Middleware\SetTenantContext::class,
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthContext::class,
            'client.auth' => \App\Http\Middleware\EnsureClientAuthContext::class,
        ]);

        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetTenantContext::class,
        );

        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin') || $request->is('admin/*')
                ? route('admin.login')
                : route('client.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user()?->isClientPortalAccount()) {
                return route('client.dashboard');
            }

            return route('admin.dashboard');
        });

        $middleware->web(append: [
            \App\Http\Middleware\PromoteEmbeddedWorkspaceRequest::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('commercial:expire-report-exports')->daily();
        $schedule->command('governance:process-escalations')->everyFifteenMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->boolean('_erp_modal') && $request->filled('_erp_modal_return')) {
                return redirect($request->input('_erp_modal_return'))
                    ->withErrors($exception->validator)
                    ->withInput();
            }

            return null;
        });
    })->create();
