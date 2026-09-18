<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - E-Parkir</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333333; font-size: 24px; margin-bottom: 20px; }
        p { color: #555555; line-height: 1.6; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #999999; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password E-Parkir</h1>
        <p>Anda menerima email ini karena ada permintaan reset password untuk akun Anda.</p>
        <p>Klik tombol di bawah ini untuk mereset password Anda:</p>
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
        <p>Atau copy link berikut ke browser Anda:</p>
        <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
        <p><strong>Link ini akan kadaluarsa dalam 60 menit.</strong></p>
        <div class="footer">
            <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini. Password Anda tidak akan berubah.</p>
        </div>
    </div>
</body>
</html>