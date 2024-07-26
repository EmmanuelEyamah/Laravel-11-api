<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
</head>
<body>
    <h1>Hello, {{ $user->full_name }}</h1>
    <p>Thank you for registering. Please click the link below to verify your email address:</p>
    <a href="{{ $verificationUrl }}">Verify Email</a>
</body>
</html>
