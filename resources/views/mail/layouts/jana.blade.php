<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? ($companyName ?? 'Jana Prints') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;color:#1a2744;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                <tr>
                    <td style="background:linear-gradient(135deg,#0f1b3d 0%,#1a2744 100%);border-radius:12px 12px 0 0;padding:28px 32px;text-align:center;">
                        @if (! empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="{{ $companyName ?? 'Jana Prints' }}" style="max-height:64px;max-width:220px;height:auto;width:auto;margin:0 auto 12px;display:block;">
                        @else
                            <div style="display:inline-block;width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#e91e8c,#ff6b35);color:#fff;font-weight:bold;font-size:20px;line-height:48px;margin-bottom:12px;">JP</div>
                        @endif
                        <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:0.5px;">{{ $companyName ?? 'Jana Prints' }}</h1>
                        <p style="margin:8px 0 0;color:rgba(255,255,255,0.75);font-size:13px;">Commercial Printing &bull; Branding &bull; Packaging</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff;padding:32px;border-left:1px solid #e8ecf2;border-right:1px solid #e8ecf2;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="background:#0f1b3d;border-radius:0 0 12px 12px;padding:20px 32px;text-align:center;">
                        <p style="margin:0;color:rgba(255,255,255,0.7);font-size:12px;line-height:1.6;">
                            &copy; {{ date('Y') }} {{ $companyName ?? 'Jana Prints' }}. All rights reserved.<br>
                            Commercial Printing &bull; Branding &bull; Packaging
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
