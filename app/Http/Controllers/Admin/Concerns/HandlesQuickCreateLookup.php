<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait HandlesQuickCreateLookup
{
    protected function isQuickCreateLookupRequest(?Request $request = null): bool
    {
        $request ??= request();

        return $request->boolean('_erp_lookup_create')
            || $request->header('X-Erp-Lookup-Create') === '1';
    }

    protected function quickCreateJson(int|string $id, string $label, string $message): JsonResponse
    {
        return response()->json([
            'id' => $id,
            'label' => $label,
            'value' => $id,
            'message' => $message,
        ]);
    }

    protected function quickCreateStringResponse(string $value, string $label, string $message): JsonResponse|Response
    {
        if ($this->isQuickCreateLookupRequest()) {
            return $this->quickCreateJson($value, $label, $message);
        }

        abort(404);
    }

    protected function quickCreateResponse(
        int $id,
        string $label,
        string $message,
        ?string $formView = null,
        array $formData = [],
    ): JsonResponse|Response {
        if ($this->isQuickCreateLookupRequest()) {
            return $this->quickCreateJson($id, $label, $message);
        }

        abort(404);
    }

    protected function quickCreateValidationResponse(Request $request, array $errors, string $formView, array $formData = []): Response
    {
        return response()->view($formView, array_merge($formData, [
            'errors' => $errors,
            'old' => $request->old(),
        ]), 422);
    }
}
