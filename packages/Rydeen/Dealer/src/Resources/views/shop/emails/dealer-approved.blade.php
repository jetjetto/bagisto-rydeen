<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { color: #1a1a1a; font-size: 20px; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Rydeen Dealer Portal</h1>

        <p>Hi {{ $dealer->first_name }},</p>

        <p>Your dealer account has been approved. You now have full access to the Rydeen Dealer Portal where you can browse our catalog, place orders, and access dealer resources.</p>

        <a href="{{ route('dealer.login') }}" class="btn">Log In to Your Account</a>

        <p style="margin-top: 24px;">If you have any questions, please contact us at orders@rydeenmobile.com.</p>

        <p>— Rydeen Mobile</p>
    </div>
</body>
</html>
