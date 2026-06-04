<?php

namespace App\Support\Communications\Email\Contracts;

use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\EmailProviderResult;

interface EmailProviderContract
{
    public function send(EmailAccount $account, EmailMessage $message): EmailProviderResult;
}
