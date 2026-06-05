@extends('mail.layouts.jana')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1a2744;font-weight:700;">
        New storefront quote request
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border:1px solid #e8ecf2;border-radius:8px;">
        <tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Customer</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->name }}</td></tr>
        <tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Email</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->email }}</td></tr>
        <tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Phone</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->phone }}</td></tr>
        @if ($quoteRequest->company)<tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Company</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->company }}</td></tr>@endif
        <tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Service</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->service_needed }}</td></tr>
        @if ($quoteRequest->quantity)<tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Quantity</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->quantity }}</td></tr>@endif
        @if ($quoteRequest->deadline)<tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Deadline</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $quoteRequest->deadline }}</td></tr>@endif
        @if ($quoteRequest->artwork_path)<tr><td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e8ecf2;">Artwork</td><td style="padding:10px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">Attached to this email</td></tr>@endif
    </table>

    <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#0f1b3d;">Message</p>
    <p style="margin:0 0 24px;padding:16px;background:#f8fafc;border-radius:8px;font-size:14px;line-height:1.7;color:#4a5568;white-space:pre-wrap;">{{ $quoteRequest->message }}</p>

    <a href="{{ $adminUrl }}" style="display:inline-block;background:linear-gradient(135deg,#e91e8c,#ff6b35);color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:600;">View in Admin Dashboard</a>
@endsection
