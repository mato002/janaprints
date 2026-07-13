<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures mutating admin requests that redirect without an explicit flash still
 * surface a SweetAlert-compatible status message.
 */
class EnsureAdminMutationFlash
{
    private const FLASH_KEYS = [
        'status',
        'success',
        'message',
        'error',
        'danger',
        'warning',
        'info',
        'inbox_reply_sent',
        'inbox_attachment_sent',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldAugment($request, $response)) {
            return $response;
        }

        /** @var RedirectResponse $response */
        $method = strtoupper($request->method());
        $message = match ($method) {
            'POST' => __('Saved successfully.'),
            'PUT', 'PATCH' => __('Updated successfully.'),
            'DELETE' => __('Removed successfully.'),
            default => __('Done.'),
        };

        return $response->with('status', $message);
    }

    protected function shouldAugment(Request $request, Response $response): bool
    {
        if (! $response instanceof RedirectResponse) {
            return false;
        }

        if (! $request->is('admin/*') && ! $request->is('admin')) {
            return false;
        }

        if (! in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        // Modal forms already get a default toast via HandleModalFormResponse.
        if ($request->header('Turbo-Frame') === 'erp-form-modal' || $request->boolean('_erp_modal')) {
            return false;
        }

        if ($request->session()->has('errors')) {
            return false;
        }

        foreach (self::FLASH_KEYS as $key) {
            if ($request->session()->has($key)) {
                return false;
            }
        }

        // Avoid toasting on auth / logout style redirects.
        $routeName = $request->route()?->getName() ?? '';
        if ($routeName !== '' && preg_match('/\.(login|logout|password|register)$/', $routeName) === 1) {
            return false;
        }

        return true;
    }
}
