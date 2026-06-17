<?php

namespace App\Http\Controllers\Admin\Communications\Email;

class EmailQueueController extends EmailMessageListController
{
    protected function viewName(): string
    {
        return 'admin.communications.email.queue';
    }

    protected function viewMode(): string
    {
        return 'queued';
    }
}
