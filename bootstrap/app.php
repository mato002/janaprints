<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Support\ModalFormExceptionRenderer;
use App\Support\Platform\FormGovernanceErrorClassifier;
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
            'employee.auth' => \App\Http\Middleware\EnsureEmployeeAuthContext::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/whatsapp',
            'webhooks/whatsapp/*',
        ]);

        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetTenantContext::class,
        );

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('ess') || $request->is('ess/*')) {
                return route('admin.login');
            }

            return $request->is('admin') || $request->is('admin/*')
                ? route('admin.login')
                : route('client.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if ($user?->isClientPortalAccount()) {
                return route('client.dashboard');
            }

            if ($user?->prefersEssPortal()) {
                return route('ess.dashboard');
            }

            return route('admin.dashboard');
        });

        $middleware->web(append: [
            \App\Http\Middleware\PromoteEmbeddedWorkspaceRequest::class,
            \App\Http\Middleware\RedirectToModuleWorkspaceShell::class,
            \App\Http\Middleware\HandleModalFormResponse::class,
            \App\Http\Middleware\EnsureAdminMutationFlash::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('commercial:expire-report-exports')->daily();
        $schedule->command('governance:process-escalations')->everyFifteenMinutes();
        $schedule->command('inventory:velocity:snapshot --all-windows')->dailyAt('02:30');
        $schedule->command('communications:dispatch-scheduled-events')->hourly();
        $schedule->command('communications:payment-reminders')->dailyAt('08:00');
        $schedule->command('communications:follow-up-due')->hourly();
        $schedule->command('communications:dispatch-scheduled-email-campaigns')->everyFiveMinutes();

        $schedule->command('printing:estimate:compare-actuals --limit=100')->dailyAt('01:00');
        $schedule->command('printing:profitability:generate --days=90')->dailyAt('01:30');
        $schedule->command('printing:forecast:generate')->dailyAt('02:00');
        $schedule->command('printing:advisor:generate')->dailyAt('02:15');
        $schedule->command('printing:calibration:recommend')->weeklyOn(1, '03:00');
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
            $classifier = app(FormGovernanceErrorClassifier::class);
            $presentation = $classifier->present($exception, config('app.debug'));

            if (! $returnUrl) {
                return response()->view('admin.partials.modal-form-error', [
                    'presentation' => $presentation,
                    'message' => $presentation['message'],
                    'detail' => $presentation['detail'],
                ], 422);
            }

            return ModalFormExceptionRenderer::validationResponse($exception, $request, $presentation);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            $deskFrom = in_array($request->input('from'), ['sales-desk', 'store-desk', 'designer-desk', 'production-floor'], true)
                && $request->header('Turbo-Frame') !== 'erp-form-modal';

            if (
                ! $deskFrom
                && ! $request->boolean('_erp_modal')
                && $request->header('Turbo-Frame') !== 'erp-form-modal'
            ) {
                return null;
            }

            if ($exception instanceof ValidationException) {
                if ($deskFrom) {
                    return response()->json([
                        'ok' => false,
                        'message' => $exception->getMessage(),
                        'errors' => $exception->errors(),
                    ], $exception->status);
                }

                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            if ($status < 400) {
                return null;
            }

            $classifier = app(FormGovernanceErrorClassifier::class);
            $presentation = $classifier->present($exception, config('app.debug'));

            Log::error('ERP governed form submission failed', [
                'form' => $request->route()?->getName(),
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'company_id' => session('active_company_id') ?? $request->user()?->company_id,
                'branch_id' => session('active_branch_id') ?? $request->user()?->default_branch_id,
                'payload_keys' => array_keys($request->except(['_token', 'password', 'password_confirmation'])),
                'status' => $status,
                'error_category' => $presentation['category'],
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            if ($deskFrom) {
                return response()->json([
                    'ok' => false,
                    'message' => $presentation['message'] ?: $exception->getMessage(),
                ], $status >= 400 && $status < 600 ? $status : 500);
            }

            $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

            if (! $returnUrl) {
                return response()->view('admin.partials.modal-form-error', [
                    'presentation' => $presentation,
                    'message' => $presentation['message'],
                    'detail' => $presentation['detail'],
                ], $status);
            }

            return redirect($returnUrl)
                ->withInput()
                ->with('form_error_presentation', $presentation)
                ->with('modal_error', $presentation['message']);
        });
    })->create();
