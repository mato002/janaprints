<?php

namespace App\Listeners;

use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        ActivityLogger::log('login', $event->user, $event->user->getKey());
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            ActivityLogger::log('logout', $event->user, $event->user->getKey());
        }
    }
}
