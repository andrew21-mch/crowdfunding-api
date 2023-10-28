<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <h1>Password Reset</h1>
    <p>Hello,</p>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Reset Token: {{ $token }}</p>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>