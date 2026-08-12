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
    protected static bool $rendering = false;

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

        if (! $formUrl || self::$rendering) {
            return self::errorPanel($presentation, $flatErrors->all());
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

        self::$rendering = true;

        try {
            $subRequest = Request::create($formUrl, 'GET');
            $subRequest->headers->set('Turbo-Frame', 'erp-form-modal');
            $subRequest->headers->set('Accept', 'text/html, application/xhtml+xml');
            $subRequest->setLaravelSession($request->session());
            $subRequest->setUserResolver(fn () => $request->user());

            $subResponse = app()->handle($subRequest);
            $content = is_string($subResponse->getContent()) ? $subResponse->getContent() : '';
        } catch (\Throwable) {
            return self::errorPanel($presentation, $flatErrors->all());
        } finally {
            self::$rendering = false;
        }

        // Prefer the dedicated error panel when the form URL did not re-render a modal form
        // (e.g. desk/workspace URL stored in _erp_modal_form_url).
        if (
            $subResponse->getStatusCode() >= 400
            || $content === ''
            || ! str_contains($content, 'data-erp-form-modal-panel')
        ) {
            return self::errorPanel($presentation, $flatErrors->all());
        }

        // Always ensure markers exist — ShareErrorsFromSession can miss nested app()->handle() renders.
        if ($flatErrors->isNotEmpty()) {
            $content = self::injectValidationMessages($content, $flatErrors->all(), $presentation);
        }

        if (! str_contains($content, 'data-erp-validation-message')) {
            return self::errorPanel($presentation, $flatErrors->all());
        }

        return response($content, 422, $subResponse->headers->allPreserveCaseWithoutCookies());
    }

    /**
     * @param  array<string, mixed>  $presentation
     * @param  list<string>  $validationMessages
     */
    protected static function errorPanel(array $presentation, array $validationMessages): Response
    {
        return response()->view('admin.partials.modal-form-error', [
            'presentation' => $presentation,
            'message' => $presentation['message'],
            'detail' => $presentation['detail'],
            'validationMessages' => $validationMessages,
        ], 422);
    }

    protected static function resolveFormUrl(Request $request): ?string
    {
        $inferred = self::inferFormUrlFromRoute($request);
        $formUrl = $request->input('_erp_modal_form_url');

        if (is_string($formUrl) && $formUrl !== '' && self::looksLikeFormUrl($formUrl)) {
            return $formUrl;
        }

        if ($inferred !== null) {
            return $inferred;
        }

        if (is_string($formUrl) && $formUrl !== '') {
            return $formUrl;
        }

        $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

        return is_string($returnUrl) && $returnUrl !== '' ? $returnUrl : null;
    }

    protected static function looksLikeFormUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        return (bool) preg_match('#/(create|edit)(/|$|\?)#i', $path)
            || str_contains($path, '/quick-create')
            || str_contains($path, '/from-');
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
        // Remove stale markers so re-inject always carries the current messages.
        $content = preg_replace(
            '/<div\b[^>]*\bdata-erp-validation-errors\b[^>]*>[\s\S]*?<\/div>/i',
            '',
            $content,
            1,
        ) ?? $content;

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
