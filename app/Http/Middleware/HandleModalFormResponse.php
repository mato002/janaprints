<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleModalFormResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->isModalFormRequest($request)) {
            return $response;
        }

        if ($response instanceof RedirectResponse && ! $request->session()->has('errors')) {
            $message = $request->session()->get('status')
                ?? $request->session()->get('success');

            if (! is_string($message) || $message === '') {
                $message = __('Saved successfully.');
            }

            return response()->view('admin.partials.modal-form-success', [
                'message' => $message,
                'refresh' => true,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        return $response;
    }

    protected function isModalFormRequest(Request $request): bool
    {
        if (in_array($request->input('from'), ['sales-desk', 'store-desk', 'designer-desk'], true)
            && $request->header('Turbo-Frame') !== 'erp-form-modal') {
            return false;
        }

        return $request->header('Turbo-Frame') === 'erp-form-modal'
            || $request->boolean('_erp_modal');
    }
}
