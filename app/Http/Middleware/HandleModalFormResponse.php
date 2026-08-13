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

        if ($response instanceof RedirectResponse) {
            $session = $response->getSession() ?? $request->session();
            $errorMessages = $this->flashErrorMessages($session);

            if ($errorMessages !== []) {
                $presentation = $session->get('form_error_presentation');
                if (! is_array($presentation)) {
                    $presentation = [
                        'category' => 'validation',
                        'category_label' => __('Validation Errors'),
                        'message' => $errorMessages[0],
                        'detail' => count($errorMessages) > 1 ? implode("\n", array_slice($errorMessages, 1)) : null,
                    ];
                }

                return response()->view('admin.partials.modal-form-error', [
                    'presentation' => $presentation,
                    'message' => $presentation['message'] ?? $errorMessages[0],
                    'detail' => $presentation['detail'] ?? null,
                    'validationMessages' => $errorMessages,
                ], 422);
            }

            $message = $this->flashMessageFromRedirect($response, $request);

            return response()->view('admin.partials.modal-form-success', [
                'message' => $message,
                'refresh' => true,
                'redirect' => $response->getTargetUrl(),
            ]);
        }

        return $response;
    }

    /**
     * @param  \Illuminate\Contracts\Session\Session|\Symfony\Component\HttpFoundation\Session\SessionInterface|null  $session
     * @return list<string>
     */
    protected function flashErrorMessages($session): array
    {
        if ($session === null) {
            return [];
        }

        $messages = [];

        if ($session->has('errors')) {
            $bag = $session->get('errors');
            if ($bag instanceof MessageBag) {
                $messages = array_merge($messages, $bag->all());
            }
        }

        foreach (['modal_error', 'error'] as $key) {
            $value = $session->get($key);
            if (is_string($value) && $value !== '') {
                $messages[] = $value;
            }
        }

        return array_values(array_unique(array_filter($messages, fn ($message) => is_string($message) && $message !== '')));
    }

    protected function isDeskShellFormRequest(Request $request): bool
    {
        if ($request->boolean('_erp_modal') || $request->header('Turbo-Frame') === 'erp-form-modal') {
            return false;
        }

        return in_array($request->input('from'), $this->deskShellFromValues, true);
    }

    protected function flashMessageFromRedirect(RedirectResponse $response, Request $request): string
    {
        $session = $response->getSession() ?? $request->session();

        foreach (['status', 'success', 'message'] as $key) {
            $value = $session->get($key);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return __('Saved successfully.');
    }

    protected function transformDeskShellResponse(Request $request, Response $response): Response
    {
        if (! $response instanceof RedirectResponse) {
            return $response;
        }

        if ($request->session()->has('errors') || $request->session()->has('modal_error') || $request->session()->has('error')) {
            /** @var MessageBag|mixed $bag */
            $bag = $request->session()->get('errors');
            $messages = $bag instanceof MessageBag
                ? $bag->getMessages()
                : [];
            $flat = $bag instanceof MessageBag
                ? $bag->all()
                : [];

            $sessionError = $request->session()->get('modal_error')
                ?? $request->session()->get('error');
            $message = $flat[0]
                ?? (is_string($sessionError) && $sessionError !== '' ? $sessionError : null)
                ?? __('Unable to save. Please check the form and try again.');

            $presentation = $request->session()->get('form_error_presentation');

            return response()->json([
                'ok' => false,
                'message' => is_array($presentation) && filled($presentation['message'] ?? null)
                    ? $presentation['message']
                    : $message,
                'detail' => is_array($presentation) ? ($presentation['detail'] ?? null) : null,
                'errors' => $messages !== [] ? $messages : ['form' => [$message]],
                'category' => is_array($presentation) ? ($presentation['category'] ?? null) : null,
                'category_label' => is_array($presentation) ? ($presentation['category_label'] ?? null) : null,
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
