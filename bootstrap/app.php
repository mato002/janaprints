<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            \App\Http\Middleware\RedirectToModuleWorkspaceShell::class,
            \App\Http\Middleware\HandleModalFormResponse::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('commercial:expire-report-exports')->daily();
        $schedule->command('governance:process-escalations')->everyFifteenMinutes();
        $schedule->command('inventory:velocity:snapshot --all-windows')->dailyAt('02:30');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            $isModalRequest = $request->boolean('_erp_modal')
                || $request->header('Turbo-Frame') === 'erp-form-modal';

            if (! $isModalRequest) {
                return null;
            }

            $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

            if (! $returnUrl) {
                return null;
            }

            return redirect($returnUrl)
                ->withErrors($exception->validator)
                ->withInput();
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->boolean('_erp_modal') && $request->header('Turbo-Frame') !== 'erp-form-modal') {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            if ($status < 500) {
                return null;
            }

            Log::error('ERP modal form submission failed', [
                'form' => $request->route()?->getName(),
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'company_id' => session('active_company_id') ?? $request->user()?->company_id,
                'branch_id' => session('active_branch_id') ?? $request->user()?->default_branch_id,
                'payload_keys' => array_keys($request->except(['_token', 'password', 'password_confirmation'])),
                'status' => $status,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

            if (! $returnUrl) {
                return response()->view('admin.partials.modal-form-error', [
                    'message' => __('Unable to save this form right now. Please try again or contact support if the problem continues.'),
                    'detail' => config('app.debug') ? $exception->getMessage() : null,
                ], $status);
            }

            $userMessage = config('app.debug')
                ? class_basename($exception).': '.$exception->getMessage()
                : __('Unable to save this form right now. Please check your entries and try again.');

            return redirect($returnUrl)
                ->withInput()
                ->with('modal_error', $userMessage);
        });
    })->create();
