<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;background:#f7f8fa;color:#111214;font-family:Arial,sans-serif;">
@if ($preheader)
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader }}</div>
@endif
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f7f8fa;padding:28px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e6e8eb;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px;border-bottom:1px solid #e6e8eb;">
                        <img src="{{ asset('images/carikerja-logo.png') }}" alt="carikerja.asia" width="156" style="display:block;width:156px;height:auto;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;">
                        <h1 style="margin:0 0 16px;font-size:26px;line-height:1.25;color:#111214;">{{ $subject }}</h1>

                        <div style="font-size:16px;line-height:1.7;color:#3f464d;">
                            {!! nl2br(e($body)) !!}
                        </div>

                        @if ($buttonLabel && $buttonUrl)
                            <div style="margin-top:26px;">
                                <a href="{{ $buttonUrl }}" style="display:inline-block;background:#f97300;color:#ffffff;text-decoration:none;font-weight:700;border-radius:8px;padding:12px 18px;">{{ $buttonLabel }}</a>
                            </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 28px;border-top:1px solid #e6e8eb;color:#626a73;font-size:13px;line-height:1.6;">
                        Anda menerima email ini karena pernah mendaftar atau memiliki akun di carikerja.asia.
                        <br>
                        <a href="{{ $unsubscribeUrl }}" style="color:#d95f00;">Berhenti menerima email marketing</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
