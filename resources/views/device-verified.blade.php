<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Verified - OnePass</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            text-align: center;
            margin: 20px;
        }
        h1 {
            color: #5e72e4;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 64px;
            color: #2dce89;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 25px;
            font-size: 18px;
            line-height: 1.6;
        }
        .token-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
            word-break: break-all;
            font-family: monospace;
            text-align: left;
            border-left: 4px solid #5e72e4;
        }
        .button {
            display: inline-block;
            background-color: #5e72e4;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #4a5ecf;
        }
        .footer {
            margin-top: 40px;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✓</div>
        <h1>Device Verified Successfully</h1>
        <div class="message">
            {{ $message }}
            <br><br>
            Your device has been successfully verified and you now have full access to OnePass.
        </div>
        
        @if(isset($token))
        <div>
            <p>Your access token:</p>
            <div class="token-box">
                {{ $token }}
            </div>
            <p><small>Copy this token if you need to authenticate manually.</small></p>
        </div>
        @endif
        
        <a href="/" class="button">Return to Application</a>
        
        <div class="footer">
            <p>OnePass - Secure Password Manager</p>
        </div>
    </div>
</body>
</html>