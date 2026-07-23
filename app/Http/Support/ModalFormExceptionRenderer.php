<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
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
        $returnUrl = $request->input('_erp_modal_return') ?: url()->previous();

        if (! $returnUrl) {
            return response()->view('admin.partials.modal-form-error', [
                'presentation' => $presentation,
                'message' => $presentation['message'],
                'detail' => $presentation['detail'],
            ], 422);
        }

        $errorBag = new ViewErrorBag;
        $errorBag->put('default', $exception->validator->errors());

        // Nested app()->handle() runs in the same PHP request. flash() is only
        // readable on the next cycle, so use now() + View::share for the sub-render.
        $request->session()->now('errors', $errorBag);
        $request->session()->now('_old_input', $request->except(['_token', '_method']));
        $request->session()->now('form_error_presentation', $presentation);
        $request->session()->now('modal_error', $presentation['message']);
        View::share('errors', $errorBag);

        $subRequest = Request::create($returnUrl, 'GET');
        $subRequest->headers->set('Turbo-Frame', 'erp-form-modal');
        $subRequest->headers->set('Accept', 'text/html, application/xhtml+xml');
        $subRequest->setLaravelSession($request->session());
        $subRequest->setUserResolver(fn () => $request->user());

        $subResponse = app()->handle($subRequest);

        return response($subResponse->getContent(), 422, $subResponse->headers->allPreserveCaseWithoutCookies());
    }
}
