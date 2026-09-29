<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #2c7eb8;
            color: #fff;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .body {
            padding: 30px 20px;
            text-align: center;
            color: #333;
        }
        .body p {
            font-size: 16px;
            line-height: 1.5;
        }
        .code {
            display: inline-block;
            font-size: 32px;
            font-weight: bold;
            color: #2c7eb8;
            background-color: #f0f4ff;
            padding: 15px 25px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .button {
            display: inline-block;
            background-color: #2c7eb8;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 8px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #3730a3;
        }
        .footer {
            font-size: 12px;
            color: #888;
            text-align: center;
            padding: 15px 20px;
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>Password Reset Request</h1>
        </div>

        <div class="body">
            <p>Hello,</p>
            <p>We received a request to reset your password. Use the code below to complete the process:</p>

            <div class="code">{{ $code }}</div>

            <p>If you didn't request a password reset, you can safely ignore this email.</p>


        </div>

        <div class="footer">
            &copy; {{ date('Y') }} YourCompany. All rights reserved.
        </div>

    </div>
</body>
</html>
