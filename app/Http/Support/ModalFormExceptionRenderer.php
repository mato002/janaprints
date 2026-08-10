<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;

class ModalFormExceptionRenderer
{
    /**
     * Re-render the modal form with validation errors instead of redirecting.
     */
    public static function validationResponse(
        ValidationException $exception,
        Request $request,
        array $presentation,
    ): Response {
        $flatErrors = collect($exception->errors())->flatten()->filter()->values();
        $formUrl = self::resolveFormUrl($request);

        if (! $formUrl) {
            return response()->view('admin.partials.modal-form-error', [
                'presentation' => $presentation,
                'message' => $presentation['message'],
                'detail' => $presentation['detail'],
                'validationMessages' => $flatErrors->all(),
            ], 422);
        }

        $errorBag = new ViewErrorBag;
        $errorBag->put('default', $exception->validator->errors());

        // Nested app()->handle() runs in the same PHP request. flash() is only
        // readable on the next cycle, so use now() + View::share for the sub-render.
        $request->session()->now('errors', $errorBag);
        $request->session()->now('_old_input', $request->except(['_token', '_method']));
        $request->session()->now('form_error_presentation', $presentation);
        $request->session()->now('modal_error', (string) ($flatErrors->first() ?? $presentation['message']));
        View::share('errors', $errorBag);

        $subRequest = Request::create($formUrl, 'GET');
        $subRequest->headers->set('Turbo-Frame', 'erp-form-modal');
        $subRequest->headers->set('Accept', 'text/html, application/xhtml+xml');
        $subRequest->setLaravelSession($request->session());
        $subRequest->setUserResolver(fn () => $request->user());

        $subResponse = app()->handle($subRequest);
        $content = $subResponse->getContent();

        if ($flatErrors->isNotEmpty() && ! str_contains($content, 'data-erp-validation-message')) {
            $content = self::injectValidationMessages($content, $flatErrors->all(), $presentation);
        }

        return response($content, 422, $subResponse->headers->allPreserveCaseWithoutCookies());
    }

    protected static function resolveFormUrl(Request $request): ?string
    {
        $formUrl = $request->input('_erp_modal_form_url');

        if (is_string($formUrl) && $formUrl !== '') {
            return $formUrl;
        }

        $inferred = self::inferFormUrlFromRoute($request);

        if ($inferred !== null) {
            return $inferred;
        }

        $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

        return is_string($returnUrl) && $returnUrl !== '' ? $returnUrl : null;
    }

    protected static function inferFormUrlFromRoute(Request $request): ?string
    {
        $route = $request->route();

        if ($route === null) {
            return null;
        }

        $name = $route->getName();

        if (! is_string($name) || $name === '') {
            return null;
        }

        $parameters = $route->parameters();

        if (str_ends_with($name, '.store')) {
            $createRoute = Str::beforeLast($name, '.store').'.create';

            if (Route::has($createRoute)) {
                return route($createRoute, $parameters);
            }
        }

        if (str_ends_with($name, '.update')) {
            $editRoute = Str::beforeLast($name, '.update').'.edit';

            if (Route::has($editRoute)) {
                return route($editRoute, $parameters);
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $messages
     * @param  array<string, mixed>  $presentation
     */
    protected static function injectValidationMessages(string $content, array $messages, array $presentation): string
    {
        $marker = View::make('admin.partials.modal-validation-alert', [
            'validationMessages' => $messages,
            'validationPresentation' => $presentation,
        ])->render();

        if (preg_match('/<form\b[^>]*class="[^"]*erp-form-shell[^"]*"[^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertAt = $matches[0][1] + strlen($matches[0][0]);

            return substr($content, 0, $insertAt).$marker.substr($content, $insertAt);
        }

        if (str_contains($content, 'data-erp-form-modal-panel')) {
            return preg_replace(
                '/(<div\b[^>]*data-erp-form-modal-panel[^>]*>)/i',
                '$1'.$marker,
                $content,
                1,
            ) ?? ($content.$marker);
        }

        return $content.$marker;
    }
}
