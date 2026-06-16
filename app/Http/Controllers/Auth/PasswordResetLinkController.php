<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportException;

class PasswordResetLinkController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.forgot-password', [
            'portal' => $this->portal($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->string('email')->toString();

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (TransportException $exception) {
            report($exception);
            $this->clearPasswordResetToken($email);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('We could not send the reset email right now. Please try again later or contact support.'),
                ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    protected function clearPasswordResetToken(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            Password::broker()->getRepository()->delete($user);
        }
    }

    protected function portal(Request $request): string
    {
        return $request->routeIs('client.password.*') ? 'client' : 'admin';
    }
}
