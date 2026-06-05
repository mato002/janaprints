<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StorePublicQuoteRequestRequest;
use App\Services\Storefront\PublicQuoteRequestService;
use App\Support\Storefront\PublicLeadRateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class PublicQuoteRequestController extends Controller
{
    public function __construct(
        protected PublicQuoteRequestService $service,
        protected PublicLeadRateLimiter $rateLimiter,
    ) {}

    public function store(StorePublicQuoteRequestRequest $request): RedirectResponse|JsonResponse
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
            $validated = $request->validated();
            $this->service->store(
                $validated,
                $request->file('artwork'),
            );
            $this->rateLimiter->hit($request, $email);
        } catch (\Throwable $e) {
            Log::error('Public quote request submission failed.', [
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('We could not process your request. Please try again or contact us directly.'),
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['form' => __('We could not process your request. Please try again or contact us directly.')]);
        }

        return $this->respond($request);
    }

    protected function respond(StorePublicQuoteRequestRequest $request, bool $success = true): RedirectResponse|JsonResponse
    {
        $message = __('Thank you! Your quote request has been received. Our team will contact you shortly with pricing and guidance.');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $success ? 200 : 422);
        }

        return back()->with('quote_success', $message);
    }
}
