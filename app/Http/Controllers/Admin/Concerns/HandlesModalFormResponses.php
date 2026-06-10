<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Modal form success responses for controllers that return explicit modal markers.
 * Redirect-based successes are also converted globally by HandleModalFormResponse middleware.
 */
trait HandlesModalFormResponses
{
    protected function isModalFormRequest(?Request $request = null): bool
    {
        $request ??= request();

        return $request->header('Turbo-Frame') === 'erp-form-modal'
            || $request->boolean('_erp_modal');
    }

    protected function modalOrRedirect(
        string $message,
        RedirectResponse $redirect,
        bool $refreshWorkspace = true,
    ): RedirectResponse|Response {
        if (! $this->isModalFormRequest()) {
            return $redirect->with('status', $message);
        }

        return response()->view('admin.partials.modal-form-success', [
            'message' => $message,
            'refresh' => $refreshWorkspace,
        ]);
    }
}
