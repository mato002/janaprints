<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response;

class HandleModalFormResponse
{
    /**
     * @var list<string>
     */
    protected array $deskShellFromValues = [
        'sales-desk',
        'store-desk',
        'designer-desk',
        'production-floor',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Desk shells submit via fetch(redirect: 'manual'). Browsers expose redirects as
        // opaque status 0, so return JSON the client can read instead of a 302.
        if ($this->isDeskShellFormRequest($request)) {
            return $this->transformDeskShellResponse($request, $response);
        }

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

    protected function isDeskShellFormRequest(Request $request): bool
    {
        return in_array($request->input('from'), $this->deskShellFromValues, true)
            && $request->header('Turbo-Frame') !== 'erp-form-modal';
    }

    protected function transformDeskShellResponse(Request $request, Response $response): Response
    {
        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        if ($request->session()->has('errors')) {
            /** @var MessageBag|mixed $bag */
            $bag = $request->session()->get('errors');
            $messages = $bag instanceof MessageBag
                ? $bag->getMessages()
                : [];
            $flat = $bag instanceof MessageBag
                ? $bag->all()
                : [];

            return response()->json([
                'ok' => false,
                'message' => $flat[0] ?? __('Unable to save. Please check the form and try again.'),
                'errors' => $messages,
                'redirect' => $response->getTargetUrl(),
            ], 422);
        }

        $message = $request->session()->get('status')
            ?? $request->session()->get('success');

        if (! is_string($message) || $message === '') {
            $message = __('Saved successfully.');
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'redirect' => $response->getTargetUrl(),
        ]);
    }

    protected function isModalFormRequest(Request $request): bool
    {
        if ($this->isDeskShellFormRequest($request)) {
            return false;
        }

        return $request->header('Turbo-Frame') === 'erp-form-modal'
            || $request->boolean('_erp_modal');
    }
}
