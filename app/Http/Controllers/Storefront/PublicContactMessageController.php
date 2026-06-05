<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StorePublicContactMessageRequest;
use App\Services\Storefront\PublicContactMessageService;
use App\Support\Storefront\PublicLeadRateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PublicContactMessageController extends Controller
{
    public function __construct(
        protected PublicContactMessageService $service,
        protected PublicLeadRateLimiter $rateLimiter,
    ) {}

    public function store(StorePublicContactMessageRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->filled('website') || $request->filled('_gotcha')) {
            return $this->respond($request, success: true);
        }

        $email = (string) $request->validated('email');

        if ($response = $this->rateLimiter->ensureNotLimited($request, $email)) {
            if ($request->expectsJson()) {
                return $response;
            }

            return back()->withInput()->withErrors([
                'email' => __('Too many submissions. Please try again later.'),
            ]);
        }

        try {
            $this->service->store($request->validated());
            $this->rateLimiter->hit($request, $email);
        } catch (\Throwable $e) {
            Log::error('Public contact message submission failed.', [
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('We could not process your message. Please try again or contact us directly.'),
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['form' => __('We could not process your message. Please try again or contact us directly.')]);
        }

        return $this->respond($request);
    }

    protected function respond(StorePublicContactMessageRequest $request, bool $success = true): RedirectResponse|JsonResponse
    {
        $message = __('Thank you! Your message has been received. A member of our team will respond shortly.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $success ? 200 : 422);
        }

        return back()->with('contact_success', $message);
    }
}
