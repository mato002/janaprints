<?php

namespace App\Http\Controllers\Admin\Communications\Email;

class EmailDraftsController extends EmailMessageListController
{
    protected function viewName(): string
    {
        return 'admin.communications.email.drafts';
    }

    protected function viewMode(): string
    {
        return 'drafts';
    }
}
