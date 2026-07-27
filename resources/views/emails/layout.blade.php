<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>@yield('subject')</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);">

    {{-- Header --}}
    <tr>
        <td style="background:#1e40af;padding:28px 36px;text-align:center;">
            <div style="font-size:22px;font-weight:bold;color:#ffffff;letter-spacing:1px;">
                {{ config('app.name') }}
            </div>
        </td>
    </tr>

    {{-- Body --}}
    <tr>
        <td style="padding:36px 36px 28px;">
            @yield('content')
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 36px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#94a3b8;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <p style="margin:6px 0 0;font-size:12px;color:#94a3b8;">
                This is an automated email — please do not reply directly.
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
