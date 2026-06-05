<?php

namespace App\Support\Storefront;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PublicLeadRateLimiter
{
    public function tooManyAttempts(Request $request, string $email): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey($request, $email), $this->maxAttempts());
    }

    public function hit(Request $request, string $email): void
    {
        RateLimiter::hit($this->throttleKey($request, $email), $this->decaySeconds());
    }

    public function availableIn(Request $request, string $email): int
    {
        return RateLimiter::availableIn($this->throttleKey($request, $email));
    }

    public function ensureNotLimited(Request $request, string $email): ?Response
    {
        if (! $this->tooManyAttempts($request, $email)) {
            return null;
        }

        $seconds = $this->availableIn($request, $email);

        return response()->json([
            'message' => __('Too many submissions. Please try again in :seconds seconds.', ['seconds' => $seconds]),
        ], 429);
    }

    protected function throttleKey(Request $request, string $email): string
    {
        return 'public-lead|'.sha1(strtolower($email).'|'.$request->ip());
    }

    protected function maxAttempts(): int
    {
        return config('leads.rate_limit.max_attempts', 5);
    }

    protected function decaySeconds(): int
    {
        return config('leads.rate_limit.decay_minutes', 15) * 60;
    }
}
