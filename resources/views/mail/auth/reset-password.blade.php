@extends('mail.layouts.jana')

@section('content')
    <h2 style="margin:0 0 16px;color:#0f1b3d;font-size:20px;">{{ __('Reset your password') }}</h2>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        {{ __('Hello :name,', ['name' => $userName]) }}
    </p>

    <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
        {{ __('We received a request to reset the password for your :portal account. Click the button below to choose a new password.', ['portal' => $portalLabel]) }}
    </p>

    <p style="margin:0 0 20px;text-align:center;">
        <a href="{{ $url }}" style="display:inline-block;background:#ff7a18;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:600;font-size:15px;">
            {{ __('Reset password') }}
        </a>
    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        {{ __('This link expires in :count minutes.', ['count' => $expireMinutes]) }}
    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        {{ __('If you did not request a password reset, you can safely ignore this email.') }}
    </p>

    <p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.6;">
        {{ __('If the button does not work, copy and paste this URL into your browser:') }}<br>
        <a href="{{ $url }}" style="color:#e91e8c;word-break:break-all;">{{ $url }}</a>
    </p>
@endsection
