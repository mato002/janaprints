@extends('mail.layouts.jana')

@section('content')
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#1a2744;">
        Dear {{ $contactMessage->name }},
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#4a5568;">
        Thank you for contacting Jana Prints. We have received your message and a member of our team will respond shortly.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border:1px solid #e8ecf2;border-radius:8px;">
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#64748b;width:30%;border-bottom:1px solid #e8ecf2;">Subject</td>
            <td style="padding:12px 16px;font-size:14px;color:#1a2744;border-bottom:1px solid #e8ecf2;">{{ $contactMessage->subject }}</td>
        </tr>
    </table>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#4a5568;">
        Our customer service team typically responds within one business day. For urgent enquiries, please call or message us on WhatsApp.
    </p>

    <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#0f1b3d;">Contact options</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#4a5568;">
        Email: <a href="{{ $contact['email_href'] }}" style="color:#e91e8c;">{{ $contact['email'] }}</a><br>
        Phone: <a href="{{ $contact['phone_href'] }}" style="color:#e91e8c;">{{ $contact['phone'] }}</a><br>
        WhatsApp: <a href="https://wa.me/{{ $whatsapp['number'] }}" style="color:#e91e8c;">Chat with us</a>
    </p>
@endsection
