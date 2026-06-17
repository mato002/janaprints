<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use Illuminate\View\View;

class EmailInboxController extends EmailMessageListController
{
    protected function viewName(): string
    {
        return 'admin.communications.email.inbox';
    }

    protected function viewMode(): string
    {
        return 'inbox';
    }
}
