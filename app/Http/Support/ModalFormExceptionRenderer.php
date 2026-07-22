<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $request->session()->flash('errors', $exception->validator->errors());
        $request->session()->flash('_old_input', $request->except(['_token', '_method']));
        $request->session()->flash('form_error_presentation', $presentation);
        $request->session()->flash('modal_error', $presentation['message']);

        $subRequest = Request::create($returnUrl, 'GET');
        $subRequest->headers->set('Turbo-Frame', 'erp-form-modal');
        $subRequest->headers->set('Accept', 'text/html, application/xhtml+xml');
        $subRequest->setLaravelSession($request->session());
        $subRequest->setUserResolver(fn () => $request->user());

        $subResponse = app()->handle($subRequest);

        return response($subResponse->getContent(), 422, $subResponse->headers->allPreserveCaseWithoutCookies());
    }
}
