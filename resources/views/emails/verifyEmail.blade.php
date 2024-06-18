<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #ddd;
        }

        .header img {
            max-width: 100px;
        }

        .content {
            text-align: center;
            padding: 30px 20px;
        }

        .content h1 {
            color: #333333;
            font-size: 24px;
        }

        .content p {
            color: #666666;
            line-height: 1.6;
            font-size: 16px;
        }

        .button {
            display: inline-block;
            margin: 20px 0;
            padding: 15px 25px;
            color: #ffffff;
            background-color: #007bff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #ddd;
            color: #999999;
            font-size: 12px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">

        </div>
        <div class="content">
            <h1>Welcome to Our Blog!</h1>
            <p>Thank you for joining our blogging community. To complete your registration, please verify your email address by clicking the button below.</p>
            <a href="http://127.0.0.1:8000/verifyEmail/{{ $token }}" class="button">Verify Email</a>
            {{ $token }}
            <p>If you did not sign up for this account, please disregard this email.</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 Your Blog Name. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
