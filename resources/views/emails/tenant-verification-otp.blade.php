<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Verifikasi Studio</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
    <p>Halo {{ $tenantName }},</p>

    <p>Berikut kode OTP untuk verifikasi email studio Anda di StudioKita:</p>

    <p style="font-size: 28px; font-weight: 700; letter-spacing: 6px; margin: 16px 0;">
        {{ $otpCode }}
    </p>

    <p>Kode ini berlaku sampai <strong>{{ $expiresAt }}</strong>.</p>
    <p>Jangan berikan kode ini ke siapa pun.</p>

    <p style="margin-top: 24px;">Terima kasih,<br>StudioKita</p>
</body>
</html>

