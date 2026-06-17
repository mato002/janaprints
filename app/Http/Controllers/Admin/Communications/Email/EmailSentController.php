<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use Illuminate\View\View;

class EmailSentController extends EmailMessageListController
{
    protected function viewName(): string
    {
        return 'admin.communications.email.sent';
    }

    protected function viewMode(): string
    {
        return 'sent';
    }
}
