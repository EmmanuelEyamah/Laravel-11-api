<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
</head>
<body>
    <h1>Hello, {{ $user->full_name }}</h1>
    <p>Click the link below to reset your password:</p>
    <p><a href="{{ $resetUrl }}">Click here to reset Password</a></p>
</body>
</html>
